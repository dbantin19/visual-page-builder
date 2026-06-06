<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    public function run(): void
    {
        $html = $this->buildHtml();

        $builderData = json_encode([
            'components' => [
                [
                    'tagName'    => 'div',
                    'removable'  => false,
                    'draggable'  => false,
                    'copyable'   => false,
                    'attributes' => [],
                    'components' => $html,
                ],
            ],
            'styles' => [],
        ]);

        Page::where('slug', 'home')->update([
            'content'      => $html,
            'builder_data' => $builderData,
            'is_published' => true,
        ]);
    }

    private function buildHtml(): string
    {
        return <<<'HTML'
<style>
.pgd-accordion-body{display:none;}
.pgd-accordion-body.pgd-open{display:block;}
.pgd-accordion-chevron{transition:transform .2s;flex-shrink:0;}
.pgd-accordion-chevron.pgd-open{transform:rotate(180deg);}
@media(max-width:768px){
  .pgd-hero-h1{font-size:32px!important;}
  .pgd-phone-btn{font-size:20px!important;padding:14px 28px!important;}
  .pgd-svc-card{flex:1 1 100%!important;}
  .pgd-detail-row{flex-direction:column!important;}
  .pgd-detail-row-rev{flex-direction:column!important;}
}
</style>

<!-- ══ HERO ══ -->
<section style="position:relative;min-height:540px;background:linear-gradient(rgba(8,16,32,0.74),rgba(8,16,32,0.74)),url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1600&q=80') center/cover no-repeat;display:flex;align-items:center;justify-content:center;text-align:center;padding:80px 24px;">
  <div style="max-width:780px;margin:0 auto;">
    <div style="display:inline-block;background:rgba(29,107,58,.9);color:#fff;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:6px 18px;border-radius:99px;margin-bottom:22px;">Same-Day Service Available</div>
    <h1 class="pgd-hero-h1" style="font-size:52px;font-weight:800;color:#ffffff;line-height:1.1;margin:0 0 16px;letter-spacing:-.5px;">Precision Garage Door Service</h1>
    <p style="font-size:20px;color:rgba(255,255,255,.88);margin:0 0 8px;font-weight:500;">Garage Door Repair, New Garage Doors &amp; Openers</p>
    <p style="font-size:15px;color:rgba(255,255,255,.6);margin:0 0 38px;">Serving the Greater Metro Area &mdash; Licensed &amp; Insured</p>
    <a href="tel:8000000000" class="pgd-phone-btn" style="display:inline-block;padding:18px 48px;background:#d97706;color:#fff;border-radius:10px;font-weight:800;font-size:28px;text-decoration:none;letter-spacing:-0.5px;box-shadow:0 6px 28px rgba(217,119,6,.45);">(800) 000-0000</a>
    <p style="color:rgba(255,255,255,.45);font-size:13px;margin:16px 0 0;">Available 24/7 &nbsp;&middot;&nbsp; All calls answered by a live operator</p>
  </div>
</section>

<!-- ══ SERVICE AREAS ACCORDION ══ -->
<section style="background:#1e3a5f;padding:40px 24px;">
  <div style="max-width:960px;margin:0 auto;">
    <h2 style="color:#fff;font-size:24px;font-weight:700;margin:0 0 6px;text-align:center;">Service Areas</h2>
    <p style="color:rgba(255,255,255,.55);text-align:center;margin:0 0 24px;font-size:14px;">Click a region to see coverage and contact info</p>
    <div style="border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.35);">

      <div style="border-bottom:1px solid #e5e7eb;">
        <button onclick="pgdAccordion(this)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border:none;cursor:pointer;font-size:16px;font-weight:600;color:#1e3a5f;text-align:left;transition:background .15s;" onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'">
          <span>&#128205; North County</span>
          <svg class="pgd-accordion-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="pgd-accordion-body" style="padding:16px 20px 24px;background:#f9fafb;">
          <p style="margin:0 0 8px;color:#374151;">Covering all cities in the North County area.</p>
          <a href="tel:8000000000" style="color:#1d6b3a;font-weight:700;font-size:18px;">(800) 000-0000</a>
          &nbsp;&nbsp;<a href="#" style="color:#1e3a5f;font-size:13px;text-decoration:underline;">Read customer reviews</a>
        </div>
      </div>

      <div style="border-bottom:1px solid #e5e7eb;">
        <button onclick="pgdAccordion(this)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border:none;cursor:pointer;font-size:16px;font-weight:600;color:#1e3a5f;text-align:left;transition:background .15s;" onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'">
          <span>&#128205; South District</span>
          <svg class="pgd-accordion-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="pgd-accordion-body" style="padding:16px 20px 24px;background:#f9fafb;">
          <p style="margin:0 0 8px;color:#374151;">Covering all cities in the South District area.</p>
          <a href="tel:8000000000" style="color:#1d6b3a;font-weight:700;font-size:18px;">(800) 000-0000</a>
          &nbsp;&nbsp;<a href="#" style="color:#1e3a5f;font-size:13px;text-decoration:underline;">Read customer reviews</a>
        </div>
      </div>

      <div style="border-bottom:1px solid #e5e7eb;">
        <button onclick="pgdAccordion(this)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border:none;cursor:pointer;font-size:16px;font-weight:600;color:#1e3a5f;text-align:left;transition:background .15s;" onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'">
          <span>&#128205; East Valley</span>
          <svg class="pgd-accordion-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="pgd-accordion-body" style="padding:16px 20px 24px;background:#f9fafb;">
          <p style="margin:0 0 8px;color:#374151;">Covering all cities in the East Valley area.</p>
          <a href="tel:8000000000" style="color:#1d6b3a;font-weight:700;font-size:18px;">(800) 000-0000</a>
          &nbsp;&nbsp;<a href="#" style="color:#1e3a5f;font-size:13px;text-decoration:underline;">Read customer reviews</a>
        </div>
      </div>

      <div style="border-bottom:1px solid #e5e7eb;">
        <button onclick="pgdAccordion(this)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border:none;cursor:pointer;font-size:16px;font-weight:600;color:#1e3a5f;text-align:left;transition:background .15s;" onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'">
          <span>&#128205; West Side</span>
          <svg class="pgd-accordion-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="pgd-accordion-body" style="padding:16px 20px 24px;background:#f9fafb;">
          <p style="margin:0 0 8px;color:#374151;">Covering all cities in the West Side area.</p>
          <a href="tel:8000000000" style="color:#1d6b3a;font-weight:700;font-size:18px;">(800) 000-0000</a>
          &nbsp;&nbsp;<a href="#" style="color:#1e3a5f;font-size:13px;text-decoration:underline;">Read customer reviews</a>
        </div>
      </div>

      <div style="border-bottom:1px solid #e5e7eb;">
        <button onclick="pgdAccordion(this)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border:none;cursor:pointer;font-size:16px;font-weight:600;color:#1e3a5f;text-align:left;transition:background .15s;" onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'">
          <span>&#128205; Metro Central</span>
          <svg class="pgd-accordion-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="pgd-accordion-body" style="padding:16px 20px 24px;background:#f9fafb;">
          <p style="margin:0 0 8px;color:#374151;">Covering all cities in the Metro Central area.</p>
          <a href="tel:8000000000" style="color:#1d6b3a;font-weight:700;font-size:18px;">(800) 000-0000</a>
          &nbsp;&nbsp;<a href="#" style="color:#1e3a5f;font-size:13px;text-decoration:underline;">Read customer reviews</a>
        </div>
      </div>

      <div>
        <button onclick="pgdAccordion(this)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#fff;border:none;cursor:pointer;font-size:16px;font-weight:600;color:#1e3a5f;text-align:left;transition:background .15s;" onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'">
          <span>&#128205; Hillside Region</span>
          <svg class="pgd-accordion-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div class="pgd-accordion-body" style="padding:16px 20px 24px;background:#f9fafb;">
          <p style="margin:0 0 8px;color:#374151;">Covering all cities in the Hillside Region area.</p>
          <a href="tel:8000000000" style="color:#1d6b3a;font-weight:700;font-size:18px;">(800) 000-0000</a>
          &nbsp;&nbsp;<a href="#" style="color:#1e3a5f;font-size:13px;text-decoration:underline;">Read customer reviews</a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══ SERVICE CARDS ══ -->
<section style="background:#f8fafc;padding:64px 24px;">
  <div style="max-width:1200px;margin:0 auto;">
    <h2 style="text-align:center;font-size:36px;font-weight:800;color:#1e3a5f;margin:0 0 8px;">Our Services</h2>
    <p style="text-align:center;color:#64748b;margin:0 0 44px;font-size:16px;">Expert garage door solutions &mdash; done right the first time</p>
    <div style="display:flex;flex-wrap:wrap;border-radius:16px;overflow:hidden;box-shadow:0 8px 48px rgba(0,0,0,.18);">

      <!-- Card 1: Repair -->
      <div class="pgd-svc-card" style="flex:1 1 300px;min-height:360px;position:relative;overflow:hidden;background:linear-gradient(155deg,#1e3a5f 0%,#0f2744 100%);">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.82) 45%,rgba(0,0,0,.25) 100%);"></div>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;opacity:.07;">
          <svg width="180" height="180" viewBox="0 0 180 180" fill="none"><rect x="20" y="50" width="140" height="90" rx="6" stroke="white" stroke-width="5"/><line x1="20" y1="72" x2="160" y2="72" stroke="white" stroke-width="4"/><line x1="20" y1="95" x2="160" y2="95" stroke="white" stroke-width="4"/><line x1="20" y1="118" x2="160" y2="118" stroke="white" stroke-width="4"/></svg>
        </div>
        <div style="position:absolute;bottom:0;left:0;right:0;padding:32px 28px;text-align:center;">
          <div style="width:56px;height:56px;background:rgba(217,119,6,.95);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 4px 16px rgba(217,119,6,.4);">
            <svg width="28" height="28" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <h3 style="color:#fff;font-size:21px;font-weight:700;margin:0 0 10px;text-shadow:0 2px 10px rgba(0,0,0,.5);">Local Garage Door Repair</h3>
          <p style="color:rgba(255,255,255,.72);font-size:14px;margin:0 0 20px;line-height:1.55;">Springs, cables, panels, rollers &mdash; we fix it all. Same-day service available.</p>
          <a href="#" style="display:inline-block;padding:11px 26px;background:#d97706;color:#fff;border-radius:9px;font-weight:700;font-size:14px;text-decoration:none;box-shadow:0 2px 12px rgba(217,119,6,.35);">Book Online Now</a>
        </div>
      </div>

      <!-- Card 2: New Doors -->
      <div class="pgd-svc-card" style="flex:1 1 300px;min-height:360px;position:relative;overflow:hidden;background:linear-gradient(155deg,#14532d 0%,#0a3a1f 100%);">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.82) 45%,rgba(0,0,0,.25) 100%);"></div>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;opacity:.07;">
          <svg width="180" height="180" viewBox="0 0 180 180" fill="none"><rect x="20" y="20" width="140" height="140" rx="6" stroke="white" stroke-width="5"/><rect x="40" y="40" width="100" height="100" rx="4" stroke="white" stroke-width="4"/></svg>
        </div>
        <div style="position:absolute;bottom:0;left:0;right:0;padding:32px 28px;text-align:center;">
          <div style="width:56px;height:56px;background:rgba(217,119,6,.95);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 4px 16px rgba(217,119,6,.4);">
            <svg width="28" height="28" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
          </div>
          <h3 style="color:#fff;font-size:21px;font-weight:700;margin:0 0 10px;text-shadow:0 2px 10px rgba(0,0,0,.5);">New Garage Door Replacement &amp; Installation</h3>
          <p style="color:rgba(255,255,255,.72);font-size:14px;margin:0 0 20px;line-height:1.55;">Upgrade your home with hundreds of door styles, colors, and materials.</p>
          <a href="#" style="display:inline-block;padding:11px 26px;background:#d97706;color:#fff;border-radius:9px;font-weight:700;font-size:14px;text-decoration:none;box-shadow:0 2px 12px rgba(217,119,6,.35);">Book Online Now</a>
        </div>
      </div>

      <!-- Card 3: Openers -->
      <div class="pgd-svc-card" style="flex:1 1 300px;min-height:360px;position:relative;overflow:hidden;background:linear-gradient(155deg,#4c1d95 0%,#2d0f63 100%);">
        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.82) 45%,rgba(0,0,0,.25) 100%);"></div>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;opacity:.07;">
          <svg width="180" height="180" viewBox="0 0 180 180" fill="none"><circle cx="90" cy="70" r="45" stroke="white" stroke-width="5"/><line x1="90" y1="115" x2="90" y2="155" stroke="white" stroke-width="6"/></svg>
        </div>
        <div style="position:absolute;bottom:0;left:0;right:0;padding:32px 28px;text-align:center;">
          <div style="width:56px;height:56px;background:rgba(217,119,6,.95);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 4px 16px rgba(217,119,6,.4);">
            <svg width="28" height="28" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
          </div>
          <h3 style="color:#fff;font-size:21px;font-weight:700;margin:0 0 10px;text-shadow:0 2px 10px rgba(0,0,0,.5);">New Garage Door Openers &amp; Repair</h3>
          <p style="color:rgba(255,255,255,.72);font-size:14px;margin:0 0 20px;line-height:1.55;">LiftMaster, Chamberlain, Genie &mdash; all major brands installed and repaired.</p>
          <a href="#" style="display:inline-block;padding:11px 26px;background:#d97706;color:#fff;border-radius:9px;font-weight:700;font-size:14px;text-decoration:none;box-shadow:0 2px 12px rgba(217,119,6,.35);">Book Online Now</a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══ OWNER TESTIMONIALS + COUPONS ══ -->
<section style="background:#fff;padding:72px 24px;">
  <div style="max-width:1100px;margin:0 auto;">
    <h2 style="text-align:center;font-size:32px;font-weight:800;color:#1e3a5f;margin:0 0 48px;">Meet Our Owners</h2>
    <div style="display:flex;flex-wrap:wrap;gap:28px;align-items:flex-start;">

      <!-- Owner 1 -->
      <div style="flex:1 1 260px;background:#f0f7ff;border-radius:16px;padding:32px 24px;text-align:center;">
        <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#1e3a5f,#3b82f6);margin:0 auto 16px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(30,58,95,.3);">
          <span style="color:#fff;font-size:34px;font-weight:800;">JD</span>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#1e3a5f;margin:0 0 4px;">John Davis</h3>
        <p style="color:#d97706;font-size:13px;font-weight:600;margin:0 0 16px;">Owner &amp; Master Technician</p>
        <p style="color:#475569;font-size:14px;line-height:1.72;margin:0;">&ldquo;We built this company on the belief that every customer deserves fast, honest, and professional service. With over 20 years of experience, we stand behind every repair we make.&rdquo;</p>
      </div>

      <!-- Coupon 1 -->
      <div style="flex:0 1 200px;background:linear-gradient(145deg,#1e3a5f,#0f2744);border-radius:16px;padding:32px 20px;text-align:center;border:2px dashed rgba(255,255,255,.2);position:relative;overflow:hidden;">
        <div style="position:absolute;top:-24px;right:-24px;width:90px;height:90px;background:rgba(217,119,6,.15);border-radius:50%;"></div>
        <div style="position:absolute;bottom:-20px;left:-20px;width:70px;height:70px;background:rgba(217,119,6,.1);border-radius:50%;"></div>
        <p style="color:rgba(255,255,255,.65);font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin:0 0 8px;position:relative;">Limited Time</p>
        <p style="color:#fbbf24;font-size:56px;font-weight:900;margin:0;line-height:1;position:relative;">$25</p>
        <p style="color:#fff;font-size:15px;font-weight:700;margin:6px 0 14px;position:relative;">OFF Any Repair</p>
        <hr style="border:none;border-top:1px dashed rgba(255,255,255,.2);margin:14px 0;">
        <p style="color:rgba(255,255,255,.42);font-size:11px;margin:0;position:relative;">Not valid with other offers.<br>Present at time of service.</p>
      </div>

      <!-- Owner 2 -->
      <div style="flex:1 1 260px;background:#f0f7ff;border-radius:16px;padding:32px 24px;text-align:center;">
        <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#1d6b3a,#22c55e);margin:0 auto 16px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(29,107,58,.3);">
          <span style="color:#fff;font-size:34px;font-weight:800;">SM</span>
        </div>
        <h3 style="font-size:20px;font-weight:700;color:#1e3a5f;margin:0 0 4px;">Sarah Miller</h3>
        <p style="color:#d97706;font-size:13px;font-weight:600;margin:0 0 16px;">Co-Owner &amp; Operations Director</p>
        <p style="color:#475569;font-size:14px;line-height:1.72;margin:0;">&ldquo;Our team is on call 24/7 because we understand that a broken garage door can&rsquo;t wait. We&rsquo;ve served thousands of homeowners in the area &mdash; and we&rsquo;re proud of every 5-star review.&rdquo;</p>
      </div>

      <!-- Coupon 2 -->
      <div style="flex:0 1 200px;background:linear-gradient(145deg,#1d6b3a,#0a3a1f);border-radius:16px;padding:32px 20px;text-align:center;border:2px dashed rgba(255,255,255,.2);position:relative;overflow:hidden;">
        <div style="position:absolute;top:-24px;right:-24px;width:90px;height:90px;background:rgba(217,119,6,.15);border-radius:50%;"></div>
        <div style="position:absolute;bottom:-20px;left:-20px;width:70px;height:70px;background:rgba(217,119,6,.1);border-radius:50%;"></div>
        <p style="color:rgba(255,255,255,.65);font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin:0 0 8px;position:relative;">New Customer</p>
        <p style="color:#fbbf24;font-size:56px;font-weight:900;margin:0;line-height:1;position:relative;">$50</p>
        <p style="color:#fff;font-size:15px;font-weight:700;margin:6px 0 14px;position:relative;">OFF New Door Install</p>
        <hr style="border:none;border-top:1px dashed rgba(255,255,255,.2);margin:14px 0;">
        <p style="color:rgba(255,255,255,.42);font-size:11px;margin:0;position:relative;">First-time customers only.<br>Some exclusions apply.</p>
      </div>

    </div>
  </div>
</section>

<!-- ══ ONLINE SHOWROOM ══ -->
<section style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);padding:64px 24px;">
  <div style="max-width:920px;margin:0 auto;text-align:center;">
    <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:52px 40px;">
      <div style="width:76px;height:76px;background:rgba(217,119,6,.95);border-radius:50%;margin:0 auto 24px;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 28px rgba(217,119,6,.45);">
        <svg width="36" height="36" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
      </div>
      <h2 style="color:#fff;font-size:36px;font-weight:800;margin:0 0 14px;letter-spacing:-.3px;">Browse Our Online Showroom</h2>
      <p style="color:rgba(255,255,255,.7);font-size:17px;line-height:1.65;max-width:580px;margin:0 auto 12px;">Sort garage doors by style, price, color, and more. Find the perfect door to complement your home&rsquo;s architecture.</p>
      <p style="color:rgba(255,255,255,.42);font-size:14px;margin:0 0 32px;">Hundreds of styles &nbsp;&middot;&nbsp; Multiple price points &nbsp;&middot;&nbsp; Instant visualization</p>
      <a href="#" style="display:inline-block;padding:15px 44px;background:#d97706;color:#fff;border-radius:10px;font-weight:700;font-size:18px;text-decoration:none;box-shadow:0 6px 24px rgba(217,119,6,.45);">View Now &rarr;</a>
    </div>
  </div>
</section>

<!-- ══ SERVICE DETAIL SECTIONS ══ -->
<section style="background:#fff;padding:88px 24px;">
  <div style="max-width:1100px;margin:0 auto;display:flex;flex-direction:column;gap:72px;">

    <!-- Detail: Repair -->
    <div class="pgd-detail-row" style="display:flex;flex-wrap:wrap;gap:52px;align-items:center;">
      <div style="flex:1 1 300px;min-height:300px;background:linear-gradient(145deg,#1e3a5f,#3b82f6);border-radius:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 40px rgba(30,58,95,.28);">
        <svg width="96" height="96" fill="none" stroke="rgba(255,255,255,.85)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
      </div>
      <div style="flex:2 1 300px;">
        <span style="display:inline-block;background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:5px 14px;border-radius:99px;margin-bottom:16px;">Repair Services</span>
        <h2 style="font-size:32px;font-weight:800;color:#1e3a5f;margin:0 0 16px;line-height:1.2;">Fast, Reliable Garage Door Repair</h2>
        <p style="color:#475569;font-size:16px;line-height:1.78;margin:0 0 20px;">When your garage door breaks, you need it fixed fast. Our technicians arrive with thousands of parts on board to complete most repairs in a single visit &mdash; from broken springs and snapped cables to dented panels and failed openers.</p>
        <ul style="list-style:none;padding:0;margin:0 0 28px;display:flex;flex-direction:column;gap:10px;">
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Broken spring replacement (torsion &amp; extension)</li>
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Cable repair &amp; replacement</li>
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Panel replacement &amp; dent repair</li>
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Track alignment &amp; roller replacement</li>
        </ul>
        <a href="#" style="display:inline-block;padding:12px 30px;background:#1e3a5f;color:#fff;border-radius:9px;font-weight:700;font-size:15px;text-decoration:none;">Schedule a Repair &rarr;</a>
      </div>
    </div>

    <!-- Detail: New Doors -->
    <div class="pgd-detail-row-rev" style="display:flex;flex-wrap:wrap;gap:52px;align-items:center;flex-direction:row-reverse;">
      <div style="flex:1 1 300px;min-height:300px;background:linear-gradient(145deg,#14532d,#22c55e);border-radius:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 40px rgba(20,83,45,.28);">
        <svg width="96" height="96" fill="none" stroke="rgba(255,255,255,.85)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
      </div>
      <div style="flex:2 1 300px;">
        <span style="display:inline-block;background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:5px 14px;border-radius:99px;margin-bottom:16px;">New Installations</span>
        <h2 style="font-size:32px;font-weight:800;color:#1e3a5f;margin:0 0 16px;line-height:1.2;">New Garage Door Replacement &amp; Installation</h2>
        <p style="color:#475569;font-size:16px;line-height:1.78;margin:0 0 20px;">A new garage door is one of the best investments you can make in your home. It improves curb appeal, security, and energy efficiency. We carry hundreds of styles from the top manufacturers &mdash; installed in just a few hours.</p>
        <ul style="list-style:none;padding:0;margin:0 0 28px;display:flex;flex-direction:column;gap:10px;">
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Steel, wood, aluminum &amp; fiberglass options</li>
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Insulated &amp; non-insulated styles</li>
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Custom sizes &amp; colors available</li>
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Professional installation with warranty</li>
        </ul>
        <a href="#" style="display:inline-block;padding:12px 30px;background:#1d6b3a;color:#fff;border-radius:9px;font-weight:700;font-size:15px;text-decoration:none;">Browse New Doors &rarr;</a>
      </div>
    </div>

    <!-- Detail: Openers -->
    <div class="pgd-detail-row" style="display:flex;flex-wrap:wrap;gap:52px;align-items:center;">
      <div style="flex:1 1 300px;min-height:300px;background:linear-gradient(145deg,#4c1d95,#8b5cf6);border-radius:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 40px rgba(76,29,149,.28);">
        <svg width="96" height="96" fill="none" stroke="rgba(255,255,255,.85)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
      </div>
      <div style="flex:2 1 300px;">
        <span style="display:inline-block;background:#ede9fe;color:#6d28d9;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:5px 14px;border-radius:99px;margin-bottom:16px;">Opener Services</span>
        <h2 style="font-size:32px;font-weight:800;color:#1e3a5f;margin:0 0 16px;line-height:1.2;">Garage Door Openers &amp; Repair</h2>
        <p style="color:#475569;font-size:16px;line-height:1.78;margin:0 0 20px;">Modern garage door openers offer smart home integration, battery backup, and ultra-quiet operation. Whether you need a new unit installed or your existing opener repaired, our technicians are factory-trained on all major brands.</p>
        <ul style="list-style:none;padding:0;margin:0 0 28px;display:flex;flex-direction:column;gap:10px;">
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>LiftMaster, Chamberlain &amp; Genie specialists</li>
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Smart home &amp; Wi-Fi enabled openers</li>
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Battery backup installation</li>
          <li style="color:#374151;font-size:15px;display:flex;align-items:center;gap:10px;"><span style="color:#1d6b3a;font-weight:800;font-size:18px;">&#10003;</span>Remote programming &amp; keypad setup</li>
        </ul>
        <a href="#" style="display:inline-block;padding:12px 30px;background:#4c1d95;color:#fff;border-radius:9px;font-weight:700;font-size:15px;text-decoration:none;">Explore Openers &rarr;</a>
      </div>
    </div>

  </div>
</section>

<!-- ══ WHY CHOOSE US ══ -->
<section style="background:#f0f7ff;padding:64px 24px;">
  <div style="max-width:920px;margin:0 auto;text-align:center;">
    <h2 style="font-size:34px;font-weight:800;color:#1e3a5f;margin:0 0 8px;">Why Choose Precision Garage Door?</h2>
    <p style="color:#64748b;font-size:16px;margin:0 0 44px;">We&rsquo;re not just another garage door company &mdash; we&rsquo;re your neighbors.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(248px,1fr));gap:16px;text-align:left;">
      <div style="background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,.07);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:20px;flex-shrink:0;margin-top:1px;">&#10003;</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Same Day Service</span></div>
      <div style="background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,.07);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:20px;flex-shrink:0;margin-top:1px;">&#10003;</span><span style="color:#1e293b;font-weight:600;font-size:15px;">All Calls Answered By A Live Operator 24/7</span></div>
      <div style="background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,.07);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:20px;flex-shrink:0;margin-top:1px;">&#10003;</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Licensed, Bonded &amp; Insured Technicians</span></div>
      <div style="background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,.07);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:20px;flex-shrink:0;margin-top:1px;">&#10003;</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Free, No-Obligation Service Estimates</span></div>
      <div style="background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,.07);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:20px;flex-shrink:0;margin-top:1px;">&#10003;</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Over 1 Million Satisfied Customers Nationwide</span></div>
      <div style="background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,.07);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:20px;flex-shrink:0;margin-top:1px;">&#10003;</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Serving Residents &amp; Businesses Since 2004</span></div>
      <div style="background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,.07);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:20px;flex-shrink:0;margin-top:1px;">&#10003;</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Industry-Leading Warranty On All Work</span></div>
      <div style="background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,.07);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:20px;flex-shrink:0;margin-top:1px;">&#10003;</span><span style="color:#1e293b;font-weight:600;font-size:15px;">5-Star Rated On Google &amp; Yelp</span></div>
      <div style="background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 2px 14px rgba(0,0,0,.07);display:flex;align-items:flex-start;gap:14px;"><span style="color:#1d6b3a;font-size:20px;flex-shrink:0;margin-top:1px;">&#10003;</span><span style="color:#1e293b;font-weight:600;font-size:15px;">Transparent Pricing &mdash; No Hidden Fees</span></div>
    </div>
  </div>
</section>

<!-- ══ DISCOUNT BADGES ══ -->
<section style="background:#fff;padding:60px 24px;">
  <div style="max-width:920px;margin:0 auto;text-align:center;">
    <h2 style="font-size:28px;font-weight:800;color:#1e3a5f;margin:0 0 8px;">Special Discounts</h2>
    <p style="color:#64748b;font-size:15px;margin:0 0 40px;">We proudly offer discounts to those who serve and protect our community.</p>
    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:28px;">
      <div style="width:164px;text-align:center;">
        <div style="width:108px;height:108px;border-radius:50%;background:linear-gradient(145deg,#1e3a5f,#3b82f6);margin:0 auto 14px;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(30,58,95,.28);">
          <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="white" stroke-width="2"><circle cx="24" cy="16" r="9"/><path d="M6 42c0-9.9 8.1-18 18-18s18 8.1 18 18"/></svg>
        </div>
        <p style="font-weight:700;color:#1e3a5f;font-size:15px;margin:0 0 4px;">Senior Citizens</p>
        <p style="color:#d97706;font-weight:800;font-size:20px;margin:0;">10% OFF</p>
      </div>
      <div style="width:164px;text-align:center;">
        <div style="width:108px;height:108px;border-radius:50%;background:linear-gradient(145deg,#14532d,#22c55e);margin:0 auto 14px;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(20,83,45,.28);">
          <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="white" stroke-width="2"><path d="M9 13l7-7 8 4 8-4 7 7-2 15L24 40 9 28z"/><line x1="24" y1="12" x2="24" y2="32"/></svg>
        </div>
        <p style="font-weight:700;color:#1e3a5f;font-size:15px;margin:0 0 4px;">Military</p>
        <p style="color:#d97706;font-weight:800;font-size:20px;margin:0;">10% OFF</p>
      </div>
      <div style="width:164px;text-align:center;">
        <div style="width:108px;height:108px;border-radius:50%;background:linear-gradient(145deg,#b45309,#f59e0b);margin:0 auto 14px;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(180,83,9,.28);">
          <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="white" stroke-width="2"><path d="M24 6l5 11 12 1L32 26l3 12-11-7-11 7 3-12-9-8 12-1z"/></svg>
        </div>
        <p style="font-weight:700;color:#1e3a5f;font-size:15px;margin:0 0 4px;">First Responders</p>
        <p style="color:#d97706;font-weight:800;font-size:20px;margin:0;">10% OFF</p>
      </div>
      <div style="width:164px;text-align:center;">
        <div style="width:108px;height:108px;border-radius:50%;background:linear-gradient(145deg,#4c1d95,#8b5cf6);margin:0 auto 14px;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 24px rgba(76,29,149,.28);">
          <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="white" stroke-width="2"><path d="M8 12h32M8 20h32M8 28h20"/><circle cx="34" cy="34" r="8"/><path d="M31 34h6M34 31v6"/></svg>
        </div>
        <p style="font-weight:700;color:#1e3a5f;font-size:15px;margin:0 0 4px;">Educators</p>
        <p style="color:#d97706;font-weight:800;font-size:20px;margin:0;">10% OFF</p>
      </div>
    </div>
  </div>
</section>

<!-- ══ FOOTER BANNER ══ -->
<section style="background:linear-gradient(145deg,#1e3a5f 0%,#0f2744 100%);padding:88px 24px;text-align:center;position:relative;overflow:hidden;">
  <div data-poseidon-decoration="background-pattern" data-gjs-selectable="false" data-gjs-hoverable="false" aria-hidden="true" style="position:absolute;inset:0;opacity:.04;background:repeating-linear-gradient(45deg,#fff,#fff 2px,transparent 2px,transparent 22px);pointer-events:none;"></div>
  <div style="position:relative;max-width:780px;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:center;gap:20px;margin-bottom:24px;">
      <div style="height:2px;background:rgba(217,119,6,.55);width:90px;border-radius:1px;"></div>
      <svg width="52" height="52" viewBox="0 0 52 52" fill="none">
        <rect x="4" y="15" width="44" height="24" rx="3" stroke="#d97706" stroke-width="2.5"/>
        <line x1="4" y1="23" x2="48" y2="23" stroke="#d97706" stroke-width="2"/>
        <line x1="4" y1="31" x2="48" y2="31" stroke="#d97706" stroke-width="2"/>
        <rect x="16" y="39" width="20" height="9" rx="1.5" stroke="#d97706" stroke-width="2"/>
      </svg>
      <div style="height:2px;background:rgba(217,119,6,.55);width:90px;border-radius:1px;"></div>
    </div>
    <h2 style="color:#ffffff;font-size:46px;font-weight:900;margin:0 0 14px;line-height:1.08;letter-spacing:-.5px;">Precision Garage Door</h2>
    <p style="color:#fbbf24;font-size:24px;font-weight:700;margin:0 0 24px;letter-spacing:.02em;">We Fix Garage Doors Right!</p>
    <p style="color:rgba(255,255,255,.55);font-size:14px;margin:0 0 38px;letter-spacing:.04em;">LICENSED &nbsp;&middot;&nbsp; BONDED &nbsp;&middot;&nbsp; INSURED &nbsp;&middot;&nbsp; AVAILABLE 24/7</p>
    <a href="tel:8000000000" style="display:inline-block;padding:20px 56px;background:#d97706;color:#fff;border-radius:12px;font-weight:800;font-size:28px;text-decoration:none;box-shadow:0 8px 36px rgba(217,119,6,.5);letter-spacing:-.5px;">(800) 000-0000</a>
  </div>
</section>

<script>
function pgdAccordion(btn) {
  var body    = btn.nextElementSibling;
  var chevron = btn.querySelector('.pgd-accordion-chevron');
  var isOpen  = body.classList.contains('pgd-open');
  document.querySelectorAll('.pgd-accordion-body').forEach(function(b) { b.classList.remove('pgd-open'); });
  document.querySelectorAll('.pgd-accordion-chevron').forEach(function(c) { c.classList.remove('pgd-open'); });
  if (!isOpen) {
    body.classList.add('pgd-open');
    if (chevron) chevron.classList.add('pgd-open');
  }
}
</script>
HTML;
    }
}
