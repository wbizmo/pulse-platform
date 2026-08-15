<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FormSubmissionController extends Controller
{
    public function index(Form $form): View
    {
        return view('admin.forms.submissions.index', ['form' => $form, 'submissions' => $form->submissions()->latest('created_at')->latest('id')->paginate(25)]);
    }

    public function show(Form $form, FormSubmission $submission): View
    {
        if ($submission->form_id !== $form->id) {
            throw ValidationException::withMessages(['submission' => 'The submission does not belong to this form.']);
        }

        return view('admin.forms.submissions.show', compact('form', 'submission'));
    }
}
