<?php

namespace Tests\Feature;

use App\Actions\Forms\ReorderFormFields;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FormsVerticalTest extends TestCase
{
    use RefreshDatabase;

    private function actor(bool $ok = true): User
    {
        $u = User::factory()->create(['status' => 'active']);
        if ($ok) {
            $u->roles()->attach(Role::where('name', 'super_admin')->firstOrFail());
        }

        return $u;
    }

    private function form(bool $active = true): Form
    {
        return Form::create(['name' => 'Contact', 'slug' => 'contact', 'success_message' => 'Received safely.', 'is_active' => $active]);
    }

    private function field(Form $f, array $a = []): FormField
    {
        return $f->fields()->create($a + ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'sort_order' => 0, 'configuration' => []]);
    }

    public function test_active_and_inactive_behavior(): void
    {
        $f = $this->form();
        $this->field($f);
        $this->get(route('forms.show', $f))->assertOk()->assertSee('Email');
        $f->update(['is_active' => false]);
        $this->get(route('forms.show', $f))->assertNotFound();
        $this->post(route('forms.store', $f), ['email' => 'a@b.test'])->assertNotFound();
    }

    public function test_validation_known_keys_types_and_snapshot(): void
    {
        $f = $this->form();
        $field = $this->field($f);
        $this->post(route('forms.store', $f), ['email' => 'bad'])->assertInvalid('email');
        $this->post(route('forms.store', $f), ['email' => ['bad']])->assertInvalid('email');
        $this->post(route('forms.store', $f), ['email' => 'a@b.test', 'injected' => 'x'])->assertInvalid('form');
        $this->post(route('forms.store', $f), ['email' => 'a@b.test'])->assertRedirect(route('forms.show', $f));
        $s = $f->submissions()->firstOrFail();
        $this->assertSame(['email' => 'a@b.test'], $s->values);
        $field->update(['label' => 'Renamed']);
        $field->delete();
        $this->assertSame('Email', $s->fresh()->field_snapshot[0]['label']);
    }

    public function test_closed_schema_and_duplicate_key(): void
    {
        $f = $this->form();
        $actor = $this->actor();
        $url = route('admin.forms.fields.store', $f);
        $base = ['key' => 'choice', 'label' => 'Choice', 'type' => 'select', 'configuration' => '{"options":["A","A"]}'];
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post($url, $base)->assertInvalid('configuration');
        $base['configuration'] = '{"regex":".*"}';
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post($url, $base)->assertInvalid('configuration');
        $base['type'] = 'script';
        $base['configuration'] = '{}';
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post($url, $base)->assertInvalid('type');
        $this->field($f);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->post($url, ['key' => 'email', 'label' => 'Again', 'type' => 'text', 'configuration' => '{}'])->assertInvalid('key');
    }

    public function test_parent_scoped_atomic_reorder(): void
    {
        $a = $this->form();
        $one = $this->field($a);
        $two = $a->fields()->create(['key' => 'name', 'label' => 'Name', 'type' => 'text', 'sort_order' => 1, 'configuration' => []]);
        $b = Form::create(['name' => 'Other', 'slug' => 'other', 'success_message' => 'Thanks', 'is_active' => true]);
        $foreign = $this->field($b);
        $actor = $this->actor();
        app(ReorderFormFields::class)->execute($a, [$two->id, $one->id], $actor);
        $this->assertSame([$two->id, $one->id], $a->fields()->pluck('id')->all());
        try {
            app(ReorderFormFields::class)->execute($a, [$one->id, $foreign->id], $actor);
            $this->fail();
        } catch (ValidationException) {
        }$this->assertSame([$two->id, $one->id], $a->fields()->pluck('id')->all());
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->put(route('admin.forms.fields.update', [$a, $foreign]), ['key' => 'x', 'label' => 'X', 'type' => 'text', 'configuration' => '{}'])->assertInvalid('field');
    }

    public function test_authorization_mfa_retention_and_escaping(): void
    {
        $f = $this->form();
        $f->update(['description' => '<script>alert(1)</script>']);
        $this->field($f, ['label' => '<img src=x onerror=alert(1)>']);
        $this->get(route('forms.show', $f))->assertDontSee('<script>', false)->assertDontSee('<img', false);
        $this->get(route('admin.forms'))->assertRedirect(route('login'));
        $this->actingAs($this->actor(false))->withSession(['mfa_passed' => true])->get(route('admin.forms'))->assertForbidden();
        $actor = $this->actor();
        $this->actingAs($actor)->withSession(['mfa_passed' => false])->get(route('admin.forms'))->assertRedirect(route('admin.mfa.challenge'));
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->get(route('admin.forms'))->assertOk();
        $this->post(route('forms.store', $f), ['email' => 'a@b.test']);
        $this->actingAs($actor)->withSession(['mfa_passed' => true])->delete(route('admin.forms.destroy', $f))->assertInvalid('form');
    }

    public function test_rate_limit(): void
    {
        $f = $this->form();
        $this->field($f);
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('forms.store', $f), ['email' => "a$i@b.test"])->assertRedirect();
        }$this->post(route('forms.store', $f), ['email' => 'last@b.test'])->assertTooManyRequests();
    }
}
