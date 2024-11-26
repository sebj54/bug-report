<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MorphToTest extends TestCase
{
    use RefreshDatabase;

    public function test_morph_to_has_correct_where(): void
    {
        $post = Post::factory()->create();

        $image = Image::factory()
            ->for($post, 'imageable')
            ->create();

        $relation = $image->imageable();
        $wheres = $relation->getQuery()->getQuery()->wheres;

        $this->assertContains([
            'type' => 'Basic',
            'column' => 'posts.id',
            'operator' => '=',
            'value' => $post->id,
            'boolean' => 'and',
        ], $wheres);
    }

    public function test_empty_morph_to_relation_has_correct_where(): void
    {
        $relation = (new Image())->imageable();
        $wheres = $relation->getQuery()->getQuery()->wheres;

        $this->assertNotContains([
            'type' => 'Null',
            'column' => '',
            'boolean' => 'and',
        ], $wheres);
    }

    public function test_morph_to_can_user_power_join(): void
    {
        $post = Post::factory()->create();

        $image = Image::factory()
            ->for($post, 'imageable')
            ->create();

        $results = Image::joinRelationship('imageable', morphable: Post::class)
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($image));
    }
}
