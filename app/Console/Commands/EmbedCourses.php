<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Objective;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Course;
use Illuminate\Support\Str;
use Laravel\Ai\Embeddings;
use Log;


#[Signature('app:embed-courses')]
#[Description('Embedding courses data')]
class EmbedCourses extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {

        Course::whereNull('embedding')->chunk(100, function ($courses) {
            foreach ($courses as $course) {
                $toJson = $course->toJson();

                $response = Embeddings::for([$toJson])->dimensions(1536)->generate();

                $course->embedding = $response->embeddings[0];

                $course->save();
            }


        });

        Lesson::whereNull('embedding')->chunk(100, function ($lessons) {
            foreach ($lessons as $lesson) {
                $toJson = $lesson->toJson();

                $response = Embeddings::for([$toJson])->dimensions(1536)->generate();

                $lesson->embedding = $response->embeddings[0];

                $lesson->save();
            }

        });

        Module::whereNull('embedding')->chunk(100, function ($modules) {
            foreach ($modules as $module) {
                $toJson = $module->toJson();

                $response = Embeddings::for([$toJson])->dimensions(1536)->generate();

                $module->embedding = $response->embeddings[0];

                $module->save();
            }


        });

        Objective::whereNull('embedding')->chunk(100, function ($objectives) {
            foreach ($objectives as $objective) {
                $toJson = $objective->toJson();

                $response = Embeddings::for([$toJson])->dimensions(1536)->generate();

                $objective->embedding = $response->embeddings[0];

                $objective->save();
            }


        });
    }
}
