<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavMenuItem;
use App\Models\NavSetting;
use App\Models\Page;
use Illuminate\Http\Request;

class NavMenuController extends Controller
{
    public function index()
    {
        $items = NavMenuItem::with([
            'page',
            'children' => fn($q) => $q->orderBy('sort_order')->with([
                'page',
                'children' => fn($q) => $q->orderBy('sort_order')->with([
                    'page',
                    'children' => fn($q) => $q->orderBy('sort_order')->with('page'),
                ]),
            ]),
        ])
        ->whereNull('parent_id')
        ->orderBy('sort_order')
        ->get();

        $pages = Page::orderBy('name')->get();
        $navSetting = NavSetting::get();

        $flatItems = [];
        $this->flattenItems($items, $flatItems, 0);

        return view('admin.navigation.index', compact('items', 'pages', 'flatItems', 'navSetting'));
    }

    public function saveAll(Request $request)
    {
        $request->validate([
            'alignment'              => 'required|in:left,center,right',
            'logo_position'          => 'required|in:left,center,right',
            'items'                  => 'present|array',
            'items.*.id'             => 'required|integer|exists:nav_menu_items,id',
            'items.*.parent_id'      => 'nullable|integer|exists:nav_menu_items,id',
            'items.*.sort_order'     => 'required|integer|min:0',
        ]);

        NavSetting::get()->update([
            'alignment'     => $request->input('alignment'),
            'logo_position' => $request->input('logo_position'),
        ]);

        foreach ($request->input('items') as $row) {
            NavMenuItem::where('id', $row['id'])->update([
                'parent_id'  => isset($row['parent_id']) && $row['parent_id'] !== '' ? (int) $row['parent_id'] : null,
                'sort_order' => (int) $row['sort_order'],
            ]);
        }

        return redirect()->route('admin.navigation.index')->with('success', 'Navigation saved.');
    }

    public function saveSettings(Request $request)
    {
        $request->validate(['alignment' => 'required|in:left,center,right']);
        NavSetting::get()->update(['alignment' => $request->input('alignment')]);
        return redirect()->route('admin.navigation.index')->with('success', 'Navigation alignment updated.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'label'     => 'required|string|max:255',
            'page_id'   => 'nullable|exists:pages,id',
            'url'       => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:nav_menu_items,id',
        ]);

        $parentId = $request->input('parent_id') ?: null;

        if ($parentId) {
            $parentDepth = $this->getDepth(NavMenuItem::findOrFail($parentId));
            if ($parentDepth >= 3) {
                return back()->withErrors(['parent_id' => 'Maximum nesting depth (3 levels) reached.'])->withInput();
            }
        }

        $maxOrder = NavMenuItem::where('parent_id', $parentId)->max('sort_order') ?? -1;

        NavMenuItem::create([
            'label'      => $request->input('label'),
            'page_id'    => $request->input('page_id') ?: null,
            'url'        => $request->input('page_id') ? null : $request->input('url'),
            'parent_id'  => $parentId,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.navigation.index')->with('success', 'Menu item added.');
    }

    public function update(Request $request, NavMenuItem $navMenuItem)
    {
        $request->validate([
            'label'     => 'required|string|max:255',
            'page_id'   => 'nullable|exists:pages,id',
            'url'       => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:nav_menu_items,id',
        ]);

        $newParentId = $request->input('parent_id') ?: null;

        if ($newParentId) {
            if ($newParentId == $navMenuItem->id) {
                return redirect()->route('admin.navigation.index')
                    ->with('error', 'An item cannot be its own parent.');
            }
            if ($this->isCircular((int) $newParentId, $navMenuItem->id)) {
                return redirect()->route('admin.navigation.index')
                    ->with('error', 'Cannot create a circular reference.');
            }
            $newParentDepth = $this->getDepth(NavMenuItem::findOrFail($newParentId));
            if (($newParentDepth + 1) + $this->subtreeMaxDepth($navMenuItem->id) > 3) {
                return redirect()->route('admin.navigation.index')
                    ->with('error', 'Moving this item here would exceed the 3-level depth limit.');
            }
        }

        $navMenuItem->update([
            'label'     => $request->input('label'),
            'page_id'   => $request->input('page_id') ?: null,
            'url'       => $request->input('page_id') ? null : $request->input('url'),
            'parent_id' => $newParentId,
        ]);

        return redirect()->route('admin.navigation.index')->with('success', 'Menu item updated.');
    }

    public function indent(NavMenuItem $navMenuItem)
    {
        $depth = $this->getDepth($navMenuItem);

        if ($depth >= 3) {
            return redirect()->route('admin.navigation.index')
                ->with('error', 'Maximum nesting depth (3 levels) reached.');
        }

        $prevSibling = NavMenuItem::where('parent_id', $navMenuItem->parent_id)
            ->where('sort_order', '<', $navMenuItem->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if (!$prevSibling) {
            return redirect()->route('admin.navigation.index')
                ->with('error', 'No item above to nest under.');
        }

        if (($depth + 1) + $this->subtreeMaxDepth($navMenuItem->id) > 3) {
            return redirect()->route('admin.navigation.index')
                ->with('error', 'Moving this item here would exceed the 3-level depth limit.');
        }

        $maxChild = NavMenuItem::where('parent_id', $prevSibling->id)->max('sort_order') ?? -1;
        $navMenuItem->update(['parent_id' => $prevSibling->id, 'sort_order' => $maxChild + 1]);

        return redirect()->route('admin.navigation.index')
            ->with('success', "\"{$navMenuItem->label}\" moved into sub-menu.");
    }

    public function outdent(NavMenuItem $navMenuItem)
    {
        if ($navMenuItem->parent_id === null) {
            return redirect()->route('admin.navigation.index')
                ->with('error', 'Item is already top-level.');
        }

        $parent     = NavMenuItem::find($navMenuItem->parent_id);
        $grandparentId = $parent ? $parent->parent_id : null;
        $parentOrder   = $parent ? $parent->sort_order : 0;

        // Open a gap right after the parent at the grandparent level
        NavMenuItem::where('parent_id', $grandparentId)
            ->where('sort_order', '>', $parentOrder)
            ->increment('sort_order');

        $navMenuItem->update(['parent_id' => $grandparentId, 'sort_order' => $parentOrder + 1]);

        return redirect()->route('admin.navigation.index')
            ->with('success', "\"{$navMenuItem->label}\" moved up one level.");
    }

    public function destroy(NavMenuItem $navMenuItem)
    {
        // Promote direct children one level up instead of orphaning them
        NavMenuItem::where('parent_id', $navMenuItem->id)
            ->update(['parent_id' => $navMenuItem->parent_id]);

        $navMenuItem->delete();
        return redirect()->route('admin.navigation.index')->with('success', 'Menu item removed.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:nav_menu_items,id']);
        foreach ($request->input('order') as $position => $id) {
            NavMenuItem::where('id', $id)->update(['sort_order' => $position]);
        }
        return response()->json(['success' => true]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function getDepth(NavMenuItem $item): int
    {
        $depth = 0;
        $parentId = $item->parent_id;
        while ($parentId !== null) {
            $depth++;
            $parentId = NavMenuItem::where('id', $parentId)->value('parent_id');
            if ($depth > 10) break;
        }
        return $depth;
    }

    private function subtreeMaxDepth(int $itemId): int
    {
        $childIds = NavMenuItem::where('parent_id', $itemId)->pluck('id');
        if ($childIds->isEmpty()) return 0;
        return 1 + $childIds->map(fn($id) => $this->subtreeMaxDepth($id))->max();
    }

    private function isCircular(int $newParentId, int $itemId): bool
    {
        $parentId = $newParentId;
        while ($parentId !== null) {
            if ($parentId === $itemId) return true;
            $parentId = NavMenuItem::where('id', $parentId)->value('parent_id');
        }
        return false;
    }

    private function flattenItems($items, array &$flat, int $depth): void
    {
        foreach ($items as $item) {
            $flat[] = ['item' => $item, 'depth' => $depth];
            if ($item->children->isNotEmpty()) {
                $this->flattenItems($item->children, $flat, $depth + 1);
            }
        }
    }
}
