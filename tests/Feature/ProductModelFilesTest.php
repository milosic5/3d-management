<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->operator()->create();
});

it('stores both model files on create and downloads each of them', function () {
    $this->actingAs($this->user)->post(route('products.store'), [
        'name' => 'Vaza',
        'weight_grams' => 120,
        'print_time_minutes' => 240,
        'price' => 1500,
        'is_active' => true,
        'model_file' => UploadedFile::fake()->create('vaza-body.stl', 10),
        'model_file_2' => UploadedFile::fake()->create('vaza-lid.3mf', 10),
    ])->assertRedirect(route('products.index'));

    $product = Product::firstWhere('name', 'Vaza');

    expect($product->model_file_name)->toBe('vaza-body.stl')
        ->and($product->model_file_name_2)->toBe('vaza-lid.3mf');
    Storage::disk('public')->assertExists([$product->model_file_path, $product->model_file_path_2]);

    $this->actingAs($this->user)->get(route('products.download', [$product, 1]))
        ->assertOk()
        ->assertDownload('vaza-body.stl');
    $this->actingAs($this->user)->get(route('products.download', [$product, 2]))
        ->assertOk()
        ->assertDownload('vaza-lid.3mf');
});

it('replaces the second model file on update', function () {
    $product = Product::factory()->create([
        'model_file_path_2' => UploadedFile::fake()->create('old.stl', 10)->store('models', 'public'),
        'model_file_name_2' => 'old.stl',
    ]);
    $oldPath = $product->model_file_path_2;

    $this->actingAs($this->user)->post(route('products.update', $product), [
        '_method' => 'put',
        'name' => $product->name,
        'weight_grams' => $product->weight_grams,
        'print_time_minutes' => $product->print_time_minutes,
        'price' => $product->price,
        'is_active' => true,
        'model_file_2' => UploadedFile::fake()->create('new.stl', 10),
    ])->assertRedirect(route('products.index'));

    $product->refresh();

    expect($product->model_file_name_2)->toBe('new.stl');
    Storage::disk('public')->assertMissing($oldPath);
    $this->actingAs($this->user)->get(route('products.download', [$product, 2]))
        ->assertDownload('new.stl');
});
