<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Objective;
use Auth;
use Illuminate\Http\Request;
use Log;

class CourseController extends Controller
{
    //

    public function createCourse(CourseRequest $request)
    {

        $validated = $request->validated();

        $course = Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'duration' => $validated['duration'],
            'difficulty' => $validated['difficulty'],
            'thumbnail' => $validated['thumbnail'],
            'prerequisites' => $validated['prerequisites'],
            'instructor' => Auth::user()->id
        ]);

        foreach ($validated['objectives'] as $objectiveItem) {
            Objective::create([
                'objective' => $objectiveItem,
                'course' => $course->id
            ]);
        }
        ;

        foreach ($validated['modules'] as $moduleItem) {
            $module = Module::create([
                'module' => $moduleItem['title'],
                'course' => $course->id
            ]);




            foreach ($moduleItem['lessons'] as $lesson) {

                Lesson::create([
                    'lesson' => $lesson['title'],
                    'duration' => $lesson['duration'],
                    'module' => $module->id
                ]);
            }
            ;

        }
        ;

        return response()->json(true);
    }

    public function getAllCourses(Request $request)
    {
        $courses = [];

        $courses = Course::select(['title', 'description', 'duration', 'thumbnail', 'instructor', 'category'])
            ->when($request['category'] !== 'All Categories', function ($query) use ($request) {
                $query->where('category', $request['category']);
            })->when($request['difficulty'] !== 'All Levels', function ($query) use ($request) {
                $query->where('difficulty', $request['difficulty']);
            })->when($request['search'], function ($query) use ($request) {
                $query->where('title', 'LIKE', "%{$request['search']}%");
            })->get();
        foreach ($courses as $course) {
            $instructor = $course->instructorUser['fullname'];
            $course['instructor'] = $instructor;
        }
        ;
        return response()->json($courses);
    }

    public function getCategoryList()
    {

        $categories = Course::query()->distinct()->pluck('category');

        return response()->json($categories);
    }



    public function getDifficultyList()
    {
        $difficulties = Course::query()->distinct()->pluck('difficulty');

        return response()->json($difficulties);
    }
}
