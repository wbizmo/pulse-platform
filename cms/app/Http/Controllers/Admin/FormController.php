<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Access\RecordAudit;
use App\Actions\Forms\ReorderFormFields;
use App\Actions\Forms\SaveForm;
use App\Actions\Forms\SaveFormField;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FormFieldRequest;
use App\Http\Requests\Admin\FormRequest;
use App\Http\Requests\Admin\ReorderFormFieldsRequest;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FormController extends Controller
{
    public function index(): View
    {
        return view('admin.forms.index', ['forms' => Form::withCount(['fields', 'submissions'])->orderBy('name')->orderBy('id')->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.forms.create');
    }

    public function store(FormRequest $r, SaveForm $save): RedirectResponse
    {
        $form = $save->execute(new Form, $r->validated(), $r->user());

        return redirect()->route('admin.forms.edit', $form)->with('success', 'Form created.');
    }

    public function edit(Form $form): View
    {
        return view('admin.forms.edit', ['form' => $form->load('fields')]);
    }

    public function update(FormRequest $r, Form $form, SaveForm $save): RedirectResponse
    {
        $save->execute($form, $r->validated(), $r->user());

        return back()->with('success', 'Form updated.');
    }

    public function destroy(Request $r, Form $form, RecordAudit $audit): RedirectResponse
    {
        if ($form->submissions()->exists()) {
            throw ValidationException::withMessages(['form' => 'Forms with submissions must be deactivated and retained.']);
        }DB::transaction(function () use ($r, $form, $audit) {
            $audit->execute($r->user(), 'form.deleted', $form, ['field_count' => $form->fields()->count()]);
            $form->delete();
        });

        return redirect()->route('admin.forms')->with('success', 'Form deleted.');
    }

    public function storeField(FormFieldRequest $r, Form $form, SaveFormField $save): RedirectResponse
    {
        $save->execute($form, new FormField, $r->validated(), $r->user());

        return back()->with('success', 'Field added.');
    }

    public function updateField(FormFieldRequest $r, Form $form, FormField $field, SaveFormField $save): RedirectResponse
    {
        $this->owned($form, $field);
        $save->execute($form, $field, $r->validated(), $r->user());

        return back()->with('success', 'Field updated.');
    }

    public function destroyField(Request $r, Form $form, FormField $field, RecordAudit $audit): RedirectResponse
    {
        $this->owned($form, $field);
        DB::transaction(function () use ($r, $form, $field, $audit) {
            $audit->execute($r->user(), 'form.field_deleted', $field, ['form_id' => $form->id, 'key' => $field->key]);
            $field->delete();
        });

        return back()->with('success', 'Field deleted; submission snapshots remain intact.');
    }

    public function reorder(ReorderFormFieldsRequest $r, Form $form, ReorderFormFields $action): RedirectResponse
    {
        $action->execute($form, $r->validated('fields'), $r->user());

        return back()->with('success', 'Fields reordered.');
    }

    private function owned(Form $form,FormField $field): void
    {
        if ($field->form_id !== $form->id) {
            throw ValidationException::withMessages(['field' => 'The field does not belong to this form.']);
        }
    }
}
