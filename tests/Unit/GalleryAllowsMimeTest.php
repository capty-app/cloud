<?php

use App\Models\Gallery;

it('defaults to images and videos when no allowed_mimes', function () {
    $g = new Gallery(['allowed_mimes' => null]);

    expect($g->allowsMime('image/png'))->toBeTrue();
    expect($g->allowsMime('video/mp4'))->toBeTrue();
    expect($g->allowsMime('application/pdf'))->toBeFalse();
});

it('matches wildcards like image/*', function () {
    $g = new Gallery(['allowed_mimes' => ['image/*']]);

    expect($g->allowsMime('image/jpeg'))->toBeTrue();
    expect($g->allowsMime('video/mp4'))->toBeFalse();
});

it('matches exact mime types', function () {
    $g = new Gallery(['allowed_mimes' => ['video/mp4', 'image/png']]);

    expect($g->allowsMime('image/png'))->toBeTrue();
    expect($g->allowsMime('video/mp4'))->toBeTrue();
    expect($g->allowsMime('image/jpeg'))->toBeFalse();
});

it('generates a prefixed api token', function () {
    expect(Gallery::generateToken())->toStartWith('gly_');
});

it('isPublic returns true only when visibility=public', function () {
    expect((new Gallery(['visibility' => 'public']))->isPublic())->toBeTrue();
    expect((new Gallery(['visibility' => 'private']))->isPublic())->toBeFalse();
});
