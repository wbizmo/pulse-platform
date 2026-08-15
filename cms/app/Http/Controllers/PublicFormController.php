<?php

namespace App\Http\Controllers;

use App\Actions\Forms\StoreSubmission;
use App\Domain\Forms\FieldSchema;
use App\Models\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicFormController extends Controller
{
    public function show(Form $form): View
    {
        abort_unless($form->is_active, 404);

        return view('forms.show', ['form' => $form->load('fields')]);
    }

    public function store(Request $request, Form $form, StoreSubmission $store): RedirectResponse
    {
        abort_unless($form->is_active, 404);
        $form->load('fields');
        if (strlen($request->getContent()) > 65536) {
            throw ValidationException::withMessages(['form' => 'The submission is too large.']);
        }$known = $form->fields->pluck('key')->all();
        $unexpected = array_diff(array_keys($request->except(['_token', 'website'])), $known);
        if ($unexpected) {
            throw ValidationException::withMessages(['form' => 'The submission contains unexpected fields.']);
        }if ($request->filled('website')) {
            throw ValidationException::withMessages(['form' => 'The submission could not be accepted.']);
        }$rules = [];
        foreach ($form->fields as $field) {
            $rules[$field->key] = FieldSchema::rules($field);
        }$values = Validator::make($request->only($known), $rules)->validate();
        $store->execute($form, $values);

        return redirect()->route('forms.show', $form)->with('form_success', $form->success_message);
    }
}
