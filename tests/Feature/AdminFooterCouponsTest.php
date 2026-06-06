<?php

namespace Tests\Feature;

use App\Models\FooterCoupon;
use App\Models\FooterOfficeLocation;
use App\Models\FooterSetting;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminFooterCouponsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_enable_footer_coupons_and_render_them_on_public_pages(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Garage Door Repair',
            'slug' => 'garage-door-repair',
            'is_published' => true,
            'is_indexed' => true,
            'content' => '<section>Live page content</section>',
        ]);

        $response = $this->actingAs($user)->post(route('admin.footer.save'), [
            'coupons_enabled' => '1',
            'coupons' => [
                [
                    'kicker' => 'Limited time',
                    'headline' => '$25 OFF Any Repair',
                    'description' => 'Click to print this coupon.',
                    'fine_print' => 'Not valid with other offers.',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.footer.index'));
        $response->assertSessionHas('success', 'Footer saved.');

        $this->assertTrue(FooterSetting::get()->coupons_enabled);
        $this->assertDatabaseHas('footer_coupons', [
            'headline' => '$25 OFF Any Repair',
            'sort_order' => 0,
        ]);

        $coupon = FooterCoupon::firstOrFail();

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('Live page content', false)
            ->assertSee('Printable offers')
            ->assertDontSee('<h2 class="text-2xl font-bold">Offers</h2>', false)
            ->assertSee('$25 OFF Any Repair')
            ->assertSee(route('coupons.print', $coupon), false);

        $this->get(route('coupons.print', $coupon))
            ->assertOk()
            ->assertSee('Printable coupon')
            ->assertSee('window.print()', false);
    }

    public function test_enabled_footer_coupons_requires_at_least_one_coupon(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'coupons_enabled' => '1',
            'coupons' => [],
        ])
            ->assertSessionHasErrors('coupons');
    }

    public function test_admin_can_enable_footer_location_address_and_render_it_on_public_pages(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Home',
            'slug' => 'home',
            'is_published' => true,
            'is_indexed' => true,
            'content' => '<section>Home page</section>',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'location_enabled' => '1',
            'location_name' => 'Poseidon Garage Doors',
            'location_address_line_1' => '123 Ocean Avenue',
            'location_address_line_2' => 'Suite 200',
            'location_city' => 'Miami',
            'location_region' => 'FL',
            'location_postal_code' => '33101',
            'location_phone' => '(800) 000-0000',
            'coupons_enabled' => '0',
        ])->assertRedirect(route('admin.footer.index'));

        $setting = FooterSetting::get();

        $this->assertTrue($setting->location_enabled);
        $this->assertSame('123 Ocean Avenue', $setting->location_address_line_1);

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('Main location')
            ->assertSee('Poseidon Garage Doors')
            ->assertSee('123 Ocean Avenue')
            ->assertSee('Suite 200')
            ->assertSee('Miami, FL 33101')
            ->assertSee('(800) 000-0000')
            ->assertSee('href="tel:8000000000"', false)
            ->assertSeeInOrder([
                '123 Ocean Avenue',
                'Miami, FL 33101',
                '(800) 000-0000',
            ])
            ->assertDontSee('Printable offers');
    }

    public function test_enabled_footer_location_requires_a_street_address(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'location_enabled' => '1',
            'location_name' => 'Poseidon Garage Doors',
            'location_address_line_1' => '',
            'coupons_enabled' => '0',
        ])->assertSessionHasErrors('location_address_line_1');
    }

    public function test_admin_can_add_office_locations_and_render_them_on_public_pages(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Home',
            'slug' => 'home',
            'is_published' => true,
            'is_indexed' => true,
            'content' => '<section>Home page</section>',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'location_enabled' => '0',
            'coupons_enabled' => '0',
            'office_locations' => [
                [
                    'name' => 'North office',
                    'address_line_1' => '456 Marina Road',
                    'address_line_2' => 'Suite 300',
                    'city' => 'Orlando',
                    'region' => 'FL',
                    'postal_code' => '32801',
                    'phone' => '(407) 555-0101',
                ],
                [
                    'name' => '',
                    'address_line_1' => '',
                    'address_line_2' => '',
                    'city' => '',
                    'region' => '',
                    'postal_code' => '',
                ],
                [
                    'name' => 'South office',
                    'address_line_1' => '789 Harbor Street',
                    'city' => 'Tampa',
                    'region' => 'FL',
                    'postal_code' => '33602',
                    'link_url' => '/south-office',
                ],
            ],
        ])->assertRedirect(route('admin.footer.index'));

        $this->assertSame(2, FooterOfficeLocation::count());
        $this->assertDatabaseHas('footer_office_locations', [
            'name' => 'North office',
            'sort_order' => 0,
        ]);
        $this->assertDatabaseHas('footer_office_locations', [
            'name' => 'South office',
            'sort_order' => 1,
        ]);

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('Office locations')
            ->assertSee('North office')
            ->assertSee('456 Marina Road')
            ->assertSee('Suite 300')
            ->assertSee('Orlando, FL 32801')
            ->assertSee('(407) 555-0101')
            ->assertSee('href="tel:4075550101"', false)
            ->assertSeeInOrder([
                '456 Marina Road',
                'Orlando, FL 32801',
                '(407) 555-0101',
            ])
            ->assertSee('South office')
            ->assertSee('789 Harbor Street')
            ->assertSee('Tampa, FL 33602')
            ->assertSee('href="/south-office"', false);
    }

    public function test_office_location_rows_require_a_street_address(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'location_enabled' => '0',
            'coupons_enabled' => '0',
            'office_locations' => [
                [
                    'name' => 'North office',
                    'address_line_1' => '',
                ],
            ],
        ])->assertSessionHasErrors('office_locations.0.address_line_1');
    }

    public function test_admin_can_upload_affiliation_badge_and_render_it_on_public_pages(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Home',
            'slug' => 'home',
            'is_published' => true,
            'is_indexed' => true,
            'content' => '<section>Home page</section>',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'location_enabled' => '0',
            'coupons_enabled' => '0',
            'affiliations_enabled' => '1',
            'affiliation_badge' => UploadedFile::fake()->create('IDA Badge.png', 12, 'image/png'),
            'affiliation_badge_alt' => 'IDA member badge',
            'affiliation_link_url' => 'https://example.com/affiliations',
        ])->assertRedirect(route('admin.footer.index'));

        $setting = FooterSetting::get();

        try {
            $this->assertTrue($setting->affiliations_enabled);
            $this->assertNotNull($setting->affiliation_badge_path);
            $this->assertTrue(File::exists(public_path('uploads/content/'.$setting->affiliation_badge_path)));

            $this->get(route('pages.show', $page->slug))
                ->assertOk()
                ->assertSee('Affiliations')
                ->assertSee('alt="IDA member badge"', false)
                ->assertSee('href="https://example.com/affiliations"', false)
                ->assertSee('/uploads/content/', false);
        } finally {
            if ($setting->affiliation_badge_path) {
                File::delete(public_path('uploads/content/'.$setting->affiliation_badge_path));
            }
        }
    }

    public function test_enabled_affiliations_requires_a_badge(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'location_enabled' => '0',
            'coupons_enabled' => '0',
            'affiliations_enabled' => '1',
        ])->assertSessionHasErrors('affiliation_badge');
    }

    public function test_footer_sections_render_in_the_saved_order(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Home',
            'slug' => 'home',
            'is_published' => true,
            'is_indexed' => true,
            'content' => '<section>Home page</section>',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'section_order' => ['coupons', 'main_location', 'office_locations'],
            'location_enabled' => '1',
            'location_name' => 'Poseidon Garage Doors',
            'location_address_line_1' => '123 Ocean Avenue',
            'location_city' => 'Miami',
            'location_region' => 'FL',
            'location_postal_code' => '33101',
            'coupons_enabled' => '1',
            'coupons' => [
                [
                    'headline' => '$25 OFF Any Repair',
                ],
            ],
            'office_locations' => [
                [
                    'name' => 'North office',
                    'address_line_1' => '456 Marina Road',
                    'city' => 'Orlando',
                    'region' => 'FL',
                    'postal_code' => '32801',
                ],
            ],
        ])->assertRedirect(route('admin.footer.index'));

        $this->assertSame(
            ['coupons', 'main_location', 'office_locations', 'affiliations'],
            FooterSetting::get()->normalizedSectionOrder()
        );

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSeeInOrder([
                'Printable offers',
                'Poseidon Garage Doors',
                'Office locations',
            ]);
    }

    public function test_footer_sections_render_with_saved_block_and_content_alignments(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Home',
            'slug' => 'home',
            'is_published' => true,
            'is_indexed' => true,
            'content' => '<section>Home page</section>',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'section_alignments' => [
                'main_location' => 'right',
                'office_locations' => 'center',
                'affiliations' => 'left',
                'coupons' => 'right',
            ],
            'section_content_alignments' => [
                'main_location' => 'center',
                'office_locations' => 'right',
                'affiliations' => 'left',
                'coupons' => 'left',
            ],
            'location_enabled' => '1',
            'location_name' => 'Poseidon Garage Doors',
            'location_address_line_1' => '123 Ocean Avenue',
            'location_city' => 'Miami',
            'location_region' => 'FL',
            'location_postal_code' => '33101',
            'coupons_enabled' => '1',
            'coupons' => [
                [
                    'headline' => '$25 OFF Any Repair',
                ],
            ],
            'office_locations' => [
                [
                    'name' => 'North office',
                    'address_line_1' => '456 Marina Road',
                    'city' => 'Orlando',
                    'region' => 'FL',
                    'postal_code' => '32801',
                ],
            ],
        ])->assertRedirect(route('admin.footer.index'));

        $this->assertSame([
            'main_location' => 'right',
            'office_locations' => 'center',
            'affiliations' => 'left',
            'coupons' => 'right',
        ], FooterSetting::get()->normalizedSectionAlignments());

        $this->assertSame([
            'main_location' => 'center',
            'office_locations' => 'right',
            'affiliations' => 'left',
            'coupons' => 'left',
        ], FooterSetting::get()->normalizedSectionContentAlignments());

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('max-w-xl ml-auto text-center', false)
            ->assertSee('justify-end', false)
            ->assertSee('justify-center', false)
            ->assertSee('text-center', false)
            ->assertSee('text-right', false)
            ->assertSee('items-start', false);
    }

    public function test_footer_coupon_can_show_a_custom_expiry_date(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $page = Page::create([
            'name' => 'Home',
            'slug' => 'home',
            'is_published' => true,
            'is_indexed' => true,
            'content' => '<section>Home page</section>',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'coupons_enabled' => '1',
            'coupons' => [
                [
                    'headline' => '$75 OFF New Door',
                    'expires_enabled' => '1',
                    'expires_end_of_month' => '0',
                    'expires_at' => '2026-07-15',
                ],
            ],
        ])->assertRedirect(route('admin.footer.index'));

        $coupon = FooterCoupon::firstOrFail();

        $this->assertTrue($coupon->expires_enabled);
        $this->assertFalse($coupon->expires_end_of_month);
        $this->assertSame('2026-07-15', $coupon->expires_at->format('Y-m-d'));

        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('Expires July 15, 2026');

        $this->get(route('coupons.print', $coupon))
            ->assertOk()
            ->assertSee('Expires July 15, 2026');
    }

    public function test_footer_coupon_end_of_month_expiry_updates_with_the_current_month(): void
    {
        Carbon::setTestNow('2026-06-06 12:00:00');

        try {
            $user = User::create([
                'username' => 'admin',
                'password' => 'password',
            ]);

            $page = Page::create([
                'name' => 'Home',
                'slug' => 'home',
                'is_published' => true,
                'is_indexed' => true,
                'content' => '<section>Home page</section>',
            ]);

            $this->actingAs($user)->post(route('admin.footer.save'), [
                'coupons_enabled' => '1',
                'coupons' => [
                    [
                        'headline' => '$25 OFF Any Repair',
                        'expires_enabled' => '1',
                        'expires_end_of_month' => '1',
                        'expires_at' => '',
                    ],
                ],
            ])->assertRedirect(route('admin.footer.index'));

            $coupon = FooterCoupon::firstOrFail();

            $this->assertSame('June 30, 2026', $coupon->resolvedExpiryLabel());
            $this->get(route('pages.show', $page->slug))
                ->assertOk()
                ->assertSee('Expires June 30, 2026');

            Carbon::setTestNow('2026-07-02 12:00:00');
            $this->assertSame('July 31, 2026', $coupon->fresh()->resolvedExpiryLabel());
            $this->get(route('pages.show', $page->slug))
                ->assertOk()
                ->assertSee('Expires July 31, 2026');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_enabled_coupon_expiry_requires_a_date_or_end_of_month(): void
    {
        $user = User::create([
            'username' => 'admin',
            'password' => 'password',
        ]);

        $this->actingAs($user)->post(route('admin.footer.save'), [
            'coupons_enabled' => '1',
            'coupons' => [
                [
                    'headline' => '$25 OFF Any Repair',
                    'expires_enabled' => '1',
                    'expires_end_of_month' => '0',
                    'expires_at' => '',
                ],
            ],
        ])->assertSessionHasErrors('coupons.0.expires_at');
    }
}
