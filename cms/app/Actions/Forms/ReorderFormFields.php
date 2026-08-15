<?php

namespace App\Actions\Forms;

use App\Actions\Access\RecordAudit;
use App\Models\Form;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderFormFields
{
    public function __construct(private RecordAudit $audit) {}

    public function execute(Form $form, array $ids, User $actor): void
    {
        DB::transaction(function () use ($form, $ids, $actor) {
            $actual = $form->fields()->lockForUpdate()->pluck('id')->all();
            if (count($ids) !== count($actual) || count($ids) !== count(array_unique($ids)) || array_diff($ids, $actual) || array_diff($actual, $ids)) {
                throw ValidationException::withMessages(['fields' => 'The complete field list must contain only this form’s fields.']);
            }foreach ($ids as $order => $id) {
                $form->fields()->whereKey($id)->update(['sort_order' => $order + 1000]);
            }foreach ($ids as $order => $id) {
                $form->fields()->whereKey($id)->update(['sort_order' => $order]);
            }$this->audit->execute($actor, 'form.fields_reordered', $form, ['field_ids' => $ids]);
        });
    }
}
