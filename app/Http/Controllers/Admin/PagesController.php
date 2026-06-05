<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageTemplate;
use App\Models\PageVersion;
use App\Support\ContentUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PagesController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $templates = PageTemplate::latest()->get();
        return view('admin.pages.form', ['page' => null, 'templates' => $templates]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $page = Page::create($data);

        if ($request->filled('template_id')) {
            $template = PageTemplate::find($request->integer('template_id'));
            if ($template) {
                $page->update([
                    'content'      => $template->content,
                    'builder_data' => $template->builder_data,
                ]);
                return redirect()->route('admin.pages.builder', $page)->with('success', 'Page created from template.');
            }
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(Page $page)
    {
        $templates = PageTemplate::latest()->get();
        return view('admin.pages.form', compact('page', 'templates'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validated($request, $page->id);
        $page->update($data);
        return redirect()->route('admin.pages.edit', $page)->with('success', 'Page updated.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        return view('page', compact('page'));
    }

    public function builder(Page $page)
    {
        $uploadedMedia = ContentUploads::all();

        return view('admin.pages.builder', compact('page', 'uploadedMedia'));
    }

    public function useTemplate(Request $request, Page $page)
    {
        $request->validate(['template_id' => 'required|exists:page_templates,id']);
        $template = PageTemplate::findOrFail($request->integer('template_id'));
        $page->update([
            'content'      => $template->content,
            'builder_data' => $template->builder_data,
        ]);
        return redirect()->route('admin.pages.builder', $page)->with('success', 'Template applied.');
    }

    public function saveBuilder(Request $request, Page $page)
    {
        $html = $request->input('html', '');
        $css  = $request->input('css', '');

        $content = $css
            ? "<style>\n{$css}\n</style>\n{$html}"
            : $html;

        $builderData = json_encode([
            'components' => json_decode($request->input('components', '[]')),
            'styles'     => json_decode($request->input('styles', '[]')),
        ]);

        $updates = $request->boolean('publish')
            ? [
                'content' => $content,
                'builder_data' => $builderData,
                'draft_content' => null,
                'draft_builder_data' => null,
                'is_published' => true,
            ]
            : [
                'draft_content' => $content,
                'draft_builder_data' => $builderData,
            ];

        $page->update($updates);

        PageVersion::create([
            'page_id'      => $page->id,
            'content'      => $content,
            'builder_data' => $builderData,
        ]);

        // Keep only the 10 most recent versions
        $oldest = $page->versions()->pluck('id')->slice(10);
        if ($oldest->isNotEmpty()) {
            PageVersion::whereIn('id', $oldest)->delete();
        }

        return response()->json(['success' => true]);
    }

    public function listVersions(Page $page)
    {
        $versions = $page->versions()
            ->select('id', 'created_at')
            ->get()
            ->map(fn($v) => [
                'id'         => $v->id,
                'created_at' => $v->created_at->toISOString(),
                'label'      => $v->created_at->format('M j, Y — g:i:s A'),
            ]);

        return response()->json($versions);
    }

    public function restoreVersion(Page $page, PageVersion $version)
    {
        abort_if($version->page_id !== $page->id, 404);

        $page->update([
            'draft_content'      => $version->content,
            'draft_builder_data' => $version->builder_data,
        ]);

        return response()->json(['success' => true, 'builder_data' => $version->builder_data]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:255|unique:pages,slug' . ($ignoreId ? ",{$ignoreId}" : '');

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => $slugRule,
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_published'     => 'nullable|boolean',
            'is_indexed'       => 'nullable|boolean',
            'head_section'     => 'nullable|string',
            'body_section'     => 'nullable|string',
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['is_indexed']   = $request->boolean('is_indexed');

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }
}
