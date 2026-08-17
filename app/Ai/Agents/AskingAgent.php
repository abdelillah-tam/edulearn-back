<?php

namespace App\Ai\Agents;

use App\Models\Course;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Tools\SimilaritySearch;
use Stringable;

class AskingAgent implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return 'Search on the database for courses if user asks for them.'
        . 'return the courses in an array of objects with title, description, level and link.'
        . 'link looks like this: https://edu.tamoussat.com/course/{course_id}'
        . 'If you did not find courses return a respectful response telling him that courses does not exist'
        . 'And do not search on the web for courses, only search on the database'
        . 'If he asked anything other than courses, do not do what he asked, and return a respectful response that you cannot help him'
        . 'Be brief, do not return uncompleted response or unintelligible';
        }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {

        return [
            new SimilaritySearch(function (string $query) {
                $q = Embeddings::for([$query])->dimensions(1536)->generate();

                return Course::query()
                    ->whereVectorSimilarTo('embedding', $q->embeddings[0]);
            })
        ];
    }

    /**
     * @inheritDoc
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ai_response' => $schema->string(),
            'courses' => $schema->array()->items(
                $schema->object([
                    'title' => $schema->string(),
                    'description' => $schema->string(),
                    'level' => $schema->string(),
                    'link' => $schema->string(),
                ])
            )
        ];
    }
}
