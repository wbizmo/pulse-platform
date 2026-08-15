<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Domain\Builder\BuilderDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BuilderTemplateRequest;
use App\Models\BuilderTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuilderTemplateController extends Controller
{
    public function store(BuilderTemplateRequest $request, BuilderDocument $documents, RecordAudit $audit): RedirectResponse
    {
        $document = $documents->decode($request->validated('builder_data'));
        $template = DB::transaction(function () use ($request, $document, $audit): BuilderTemplate {
            $template = BuilderTemplate::create(['uuid' => (string) Str::uuid(), 'name' => $request->validated('name'), 'document' => $document, 'schema_version' => $document['schema_version'], 'created_by' => $request->user()->id]);
            $audit->execute($request->user(), 'builder_template.created', $template, ['schema_version' => $document['schema_version']]);

            return $template;
        });

        return back()->with('success', "Reusable template “{$template->name}” created.");
    }

    public function destroy(BuilderTemplate $template, RecordAudit $audit): RedirectResponse
    {
        DB::transaction(function () use ($template, $audit): void {
            $audit->execute(request()->user(), 'builder_template.deleted', $template, ['schema_version' => $template->schema_version]);
            $template->delete();
        });

        return back()->with('success', 'Reusable template deleted. Existing snapshot insertions are unchanged.');
    }
}
