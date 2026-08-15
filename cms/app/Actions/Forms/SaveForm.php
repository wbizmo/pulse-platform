<?php

namespace App\Actions\Forms;

use App\Actions\Access\RecordAudit;
use App\Models\Form;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaveForm
{
    public function __construct(private RecordAudit $audit) {}

    public function execute(Form $form, array $data, User $actor): Form
    {
        return DB::transaction(function () use ($form, $data, $actor) {
            $new = ! $form->exists;
            $was = $form->is_active;
            $form->fill($data)->save();
            $event = $new ? 'form.created' : ($was !== $form->is_active ? ($form->is_active ? 'form.activated' : 'form.deactivated') : 'form.updated');
            $this->audit->execute($actor, $event, $form, ['fields' => array_keys($data)]);

            return $form;
        });
    }
}
