<?php

use App\Livewire\Help\Guide;
use Livewire\Livewire;

it('renders guide view with flow steps, highlights, and screen guides', function (): void {
    Livewire::test(Guide::class)
        ->assertViewIs('livewire.help.guide')
        ->assertViewHas('flowSteps', function (array $steps) {
            return count($steps) === 5
                && collect($steps)->every(fn ($step) => isset($step['title'], $step['description']));
        })
        ->assertViewHas('featureHighlights', function (array $highlights) {
            return count($highlights) === 4
                && collect($highlights)->every(function ($highlight) {
                    return isset($highlight['icon'], $highlight['title'], $highlight['description'])
                        && isset($highlight['links'])
                        && is_array($highlight['links']);
                });
        })
        ->assertViewHas('screenGuides', function (array $screens) {
            return count($screens) === 6
                && collect($screens)->every(function ($screen) {
                    return isset($screen['title'], $screen['tips'])
                        && is_array($screen['tips'])
                        && count($screen['tips']) >= 2;
                });
        });
});

it('exposes localized strings for help center sections', function (): void {
    $component = Livewire::test(Guide::class);

    $flowSteps = $component->viewData('flowSteps');
    $featureHighlights = $component->viewData('featureHighlights');
    $screenGuides = $component->viewData('screenGuides');

    expect($flowSteps[0]['title'])->toBe(__('Organize your study space'))
        ->and($featureHighlights[0]['title'])->toBe(__('Workspace'))
        ->and($screenGuides[0]['title'])->toBe(__('Dashboard'));
});
