<?php

namespace App\Repositories;

use Arafat\LaravelRepository\Repository;

use App\Models\QuizQuestion;

class QuizQuestionRepository extends Repository
{
    public static function model()
    {
        return QuizQuestion::class;
    }

    public static function findByIdOrFail(string $id): QuizQuestion
    {
        return self::findOrFail($id);
    }

    public static function storeByRequest(string $quizId, array $data): QuizQuestion
    {
        $question = QuizQuestion::create([
            'quiz_id' => $quizId,
            'title' => $data['title'],
            'grade' => $data['grade'],
        ]);

        foreach ($data['answers'] as $key => $answer) {
            $question->answers()->create([
                'title' => $answer,
                'correct' => isset($data['correct'][$key]) ? 1 : 0,
                'question_id' => $question->id,
            ]);
        }

        return $question;
    }

    public static function updateByRequest(QuizQuestion $question, array $data): void
    {
        $question->update([
            'title' => $data['title'],
            'grade' => $data['grade'],
        ]);
        // update or delete answers
        $question->answers()->delete();
        foreach ($data['answers'] as $key => $answer) {
            $question->answers()->create([
                'title' => $answer,
                'correct' => isset($data['correct'][$key]) ? 1 : 0,
                'question_id' => $question->id,
            ]);
        }
    }

    public static function destroy(QuizQuestion $question): void
    {
        $question->answers()->delete();
        $question->delete();
    }
}
