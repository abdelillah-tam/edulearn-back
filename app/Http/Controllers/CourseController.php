<?php

namespace App\Http\Controllers;

use App\Ai\Agents\AskingAgent;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Objective;
use Auth;
use Cache;
use Exception;
use Illuminate\Http\Request;
use Log;
use Storage;
use Spatie\Dropbox\Client;

class CourseController extends Controller
{
    //

    public function createCourse(CourseRequest $request)
    {

        $validated = $request->validated();

        $image_url = $this->dealWithImage($request, $validated['title']);

        $course = Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'duration' => $validated['duration'],
            'difficulty' => $validated['difficulty'],
            'thumbnail' => $image_url,
            'prerequisites' => $validated['prerequisites'],
            'instructor' => Auth::user()->id
        ]);

        foreach (json_decode($validated['objectives'], true) as $objectiveItem) {
            Objective::create([
                'objective' => $objectiveItem,
                'course' => $course->id
            ]);
        }
        ;

        foreach (json_decode($validated['modules'], true) as $moduleItem) {
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

        $query = Course::select([
            'id',
            'title',
            'description',
            'duration',
            'thumbnail',
            'instructor',
            'category'
        ])->withCount('users')
            ->with('instructorUser:id,fullname')
            ->when(Auth::check(), function ($query) {
                $query->withExists([
                    'users as is_enrolled' => function ($query) {
                        $query->where('users.id', Auth::id());
                    },

                    'instructorUser as is_instructor' => function ($query) {
                        $query->where('type', Auth::user()->type);
                    }
                ]);
            })
            ->when($request['category'] !== 'All Categories', function ($query) use ($request) {
                $query->where('category', $request['category']);
            })->when($request['difficulty'] !== 'All Levels', function ($query) use ($request) {
                $query->where('difficulty', $request['difficulty']);
            })->when($request['search'], function ($query) use ($request) {
                $query->where('title', 'LIKE', "%{$request['search']}%");
            });

        $courses = $query->paginate(10);

        return response()->json($courses);
    }

    public function getCourse(Course $course)
    {

        $query = $course
            ->loadCount('users')
            ->loadCount('lessons')
            ->load('objectives')
            ->load('instructorUser:id,fullname')
            ->load([
                'modules.lessons' => function ($query) {
                    if (Auth::check()) {
                        $query->withExists([
                            'watchers as watched' => function ($q) {
                                $q->where('user_id', Auth::user()->id);
                            }
                        ]);
                    }
                }
            ]);
        if (Auth::check()) {
            $query->loadExists([
                'users as is_enrolled' => function ($query) {
                    $query->where('users.id', Auth::id());
                },

                'instructorUser as is_instructor' => function ($query) {
                    $query->where('type', Auth::user()->type);
                }
            ]);
        }

        return response()->json($course);
    }

    public function getCoursesEnrolled()
    {
        $user = Auth()->user();

        $courses = $user->courses;

        foreach ($courses as $course) {
            $instructor = $course->instructorUser['fullname'];
            $course['instructor'] = $instructor;
        }
        ;

        return response()->json($courses);
    }


    public function enroll(Request $request)
    {
        $validated = $request->validate(['course_id' => ['required']]);

        $user = Auth::user();

        $changes = $user->courses()->syncWithoutDetaching([$validated['course_id']]);

        return response()->json(!empty($changes['attached']));
    }

    public function popularCourses()
    {
        $courses = Course::query()
            ->when(Auth::check(), function ($query) {
                $query->withExists([
                    'users as is_enrolled' => function ($query) {
                        $query->where('users.id', Auth::id());
                    },
                    'instructorUser as is_instructor' => function ($query) {
                        $query->where('type', Auth::user()->type);
                    }
                ]);
            })
            ->withCount('users')
            ->orderByDesc('users_count')
            ->limit(3)
            ->get();

        foreach ($courses as $course) {
            $course->instructor = $course->instructorUser['fullname'];
        }
        return response()->json($courses);
    }

    public function setWatched(Request $request)
    {
        $validated = $request->validate(['id' => 'required']);

        $lesson = Lesson::find($validated['id']);

        $changes = $lesson->watchers()->syncWithoutDetaching(Auth::user()->id);

        return response()->json(!empty($changes['attached']));

    }

    public function getInstructorCourses()
    {
        $user = Auth::user();

        if ($user->type == 'Instructor') {
            $courses = $user->instructor_courses()->withCount('users')->get();
            $count = $user->instructor_courses()->count();

            return response()->json([
                'courses' => $courses,
                'count' => $count
            ]);
        }

        throw new Exception();
    }

    public function testPagination()
    {
        $courses = Course::paginate(1, ['*'], 'page', null);
        return response()->json($courses);
    }

    private function dealWithImage(Request $request, $title)
    {
        $path = 'edulearn/courses/' . $request->user()->id . '/';

        $filename = $title . '.jpg';

        Storage::disk('dropbox')->putFileAs(
            $path,
            $request->file('thumbnail'),
            $filename
        );

        $client = new Client(Cache::get('dropbox_access_token'));

        try {
            $response = $client->createSharedLinkWithSettings($path . $filename);
            $url = $response['url'];
        } catch (Exception $e) {
            $links = $client->listSharedLinks($path . $filename);
            $url = $links[0]['url'];
        }

        return $final_link = str_replace('dl=0', 'raw=1', $url);

    }

    public function prompting(Request $request)
    {
        set_time_limit(60);

        $response = (new AskingAgent())->prompt($request->input('prompt'));

        return response()->json($response);
    }
}
