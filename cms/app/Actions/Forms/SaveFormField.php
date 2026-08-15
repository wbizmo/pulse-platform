<?php

namespace App\Actions\Forms;

use App\Actions\Access\RecordAudit;
use App\Domain\Forms\FieldSchema;
use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveFormField
{
    public function __construct(private RecordAudit $audit) {}

    public function execute(Form $form, FormField $field, array $data, User $actor): FormField
    {
        return DB::transaction(function () use ($form, $field, $data, $actor) {
            if (! $field->exists && $form->fields()->count() >= FieldSchema::MAX_FIELDS) {
                throw ValidationException::withMessages(['field' => 'A form may contain at most 50 fields.']);
            }$new = ! $field->exists;
            if ($new) {
                $data['sort_order'] = ($form->fields()->max('sort_order') ?? -1) + 1;
            }$field->fill($data);
            $form->fields()->save($field);
            $this->audit->execute($actor, $new ? 'form.field_created' : 'form.field_updated', $field, ['form_id' => $form->id, 'key' => $field->key]);

            return $field;
        });
    }
}
