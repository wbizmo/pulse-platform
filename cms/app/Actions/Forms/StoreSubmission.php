<?php

namespace App\Actions\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\DB;

class StoreSubmission
{
    public function execute(Form $form, array $values): FormSubmission
    {
        return DB::transaction(function () use ($form, $values) {
            $snapshot = $form->fields->map(fn ($f) => ['key' => $f->key, 'label' => $f->label, 'type' => $f->type, 'required' => $f->required, 'configuration' => $f->configuration])->values()->all();

            return $form->submissions()->create(['values' => $values, 'field_snapshot' => $snapshot]);
        });
    }
}
