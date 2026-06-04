<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageTemplate;
use Illuminate\Http\Request;

class TemplatesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $html    = $request->input('html', '');
        $css     = $request->input('css', '');
        $content = $css ? "<style>\n{$css}\n</style>\n{$html}" : $html;

        $builderData = json_encode([
            'components' => json_decode($request->input('components', '[]')),
            'styles'     => json_decode($request->input('styles', '[]')),
        ]);

        PageTemplate::create([
            'name'         => $request->input('name'),
            'content'      => $content,
            'builder_data' => $builderData,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(PageTemplate $template)
    {
        $template->delete();
        return response()->json(['success' => true]);
    }
}
