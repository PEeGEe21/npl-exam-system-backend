<?php

namespace App\Http\Controllers;

use App\CbtAnswer;
use App\CbtDifficulty;
use App\CbtOptionAnswerType;
use App\CbtOptionType;
use App\CbtQuestion;
use App\CbtQuestionTest;
use App\CbtSubAnswer;
use App\CbtSubQuestion;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CbtMainController extends Controller
{
    public function questionBankIndex(Request $request)
    {
        $data = $request->all();

        $q = (new CbtQuestion)
            ->with([
                'optionType' => function ($q) {
                    $q->select('id', 'title', 'description');
                }, 'optionAnswerType' => function ($q) {
                    $q->select('id', 'title', 'description');
                }, 'difficulty' => function ($q) {
                    $q->select('id', 'title');
                }, 'answers' => function ($q) {
                    $q->select('id', 'question_id', 'is_correct', 'value');
                }, 'topic' => function ($q) {
                    $q->select('id', 'name');
                },
                'user' => function ($q) {
                    $q->select('id', 'full_name');
                },
                'subQuestion' => function ($q) {
                    $q->select('id', 'question_id', 'option_type_id', 'is_editor',
                        'question', 'question_plain')
                        ->with(['answers']);
                }
            ]);

        if (isset($data['filter_id'])) {
            if ($data['filter_id'] == 1) {
                $q->where('user_id', $data['user_id']);
            } else if ($data['filter_id'] == 2) {
                $q->where('user_id', '!=', $data['user_id']);
            } else if ($data['filter_id'] == 4) {
                $q->where('is_exam', true);
            }
        }

        $question_list = $this->questionBankHelper($data, $q);
        $ret = ['success' => true, 'question_list' => $question_list];

        if (isset($data['get_topic'])) {
            // $ret['topics'] = (new CbtTopic)
            //     ->whereHas('topicLevels', function ($query)  {
            //         $query->where('level_id', 0);
            //     })
            //     ->select('id', 'name')
            //     ->get();
            $ret['difficulties'] = (new CbtDifficulty)
                ->select('id', 'title')
                ->get();
            $ret['optionTypes'] = (new CbtOptionType)
                ->select('id', 'title', 'description')
                ->get();
            $ret['optionAnswerTypes'] = (new CbtOptionAnswerType)
                ->select('id', 'title', 'description')
                ->get();
        }

        return json_encode($ret);
    }

    public function questionBankHelper($data, $datatableData)
    {
        $datatable = CustomController::$DataTableDeFault;
        if (isset($data['data'])) {
            $datatable = [
                'current_length' => $data['data']['length'],
                'search' => $data['data']['search']['value'],
                'order' => $data['data']['order'],
                'draw' => $data['data']['draw'],
                'page' => ($data['data']['start'] / $data['data']['length']) + 1
            ];
            unset($data['data']);
            unset($data['page']);
        }

        if (isset($datatable['search']) && !empty($datatable['search'])) {
            $datatableData->where(function ($qnew) use ($datatable) {
                $qnew->where('question_plain', 'LIKE', '%' . $datatable['search'] . '%');
                $qnew->orWhere('tags', 'LIKE', '%' . $datatable['search'] . '%')
                    ->orWhereHas('topic', function ($q) use ($datatable) {
                        $q->where('name', 'LIKE', '%' . $datatable['search'] . '%');
                    })
                    ->orWhereHas('user', function ($q) use ($datatable) {
                        $q->where('full_name', 'LIKE', '%' . $datatable['search'] . '%');
                    });
            });
        }

        if (isset($datatable['order'])) {
            switch ($datatable['order'][0]['column']) {
                case 0:
                    $datatableData = $datatableData->orderBy('id', $datatable['order'][0]['dir']);
                    break;
                case 1:
                    $datatableData = $datatableData->orderBy('question_plain', $datatable['order'][0]['dir']);
                    break;
            }
        } else
            $datatableData = $datatableData->orderBy('id', 'desc');
        $datatableData = $datatableData->paginate($datatable['current_length']);

        $datatable['index'] = 0;

        $questions = [];
        $datatableData->mapToGroups(function ($item) use (&$questions, &$datatable) {
            $temp = [];

            $temp['sn'] = ($datatable['current_length'] * ($datatable['page'] - 1)) + $datatable['index'] + 1;

            if ($item->option_type_id === 4) {
                $item->no_of_questions = count($item->subQuestion);
            }
            $main = $item->toArray();
            $main['question'] = str_replace('<p>&nbsp;</p>', '', $main['question']);
            $temp['main'] = $main;
            $questions[] = $temp;
            $datatable['index']++;
            return [];
        });
        $ret = [];
        $ret['draw'] = $datatable['draw'];
        $ret['recordsFiltered'] = $datatableData->total();
        $ret['recordsTotal'] = $datatable['page'] == 1 ? $ret['recordsFiltered'] :
            $ret['recordsFiltered'];
        $ret['data'] = $questions;

        return $ret;
    }

    public function setGeneralQuestion(Request $request)
    {
        $data = $request->all();
        $req = [
            'user_id',
            'level_id',
            'formData',
        ];

        foreach ($req as $item) {
            if (!isset($data[$item]))
                return json_encode(['error' => 'Required data not provided'], 200);
        }

        $session = $this->getSession(null);
        if (is_null($session))
            return json_encode(['error' => 'No active session found in system'], 200);

        $user = (new User)
            ->select('id', 'full_name')
            ->find($data['user_id']);
        if (is_null($user))
            return json_encode(['error' => 'User not found'], 200);

        $is_update = isset($data['update_id']);

        if (isset($data['is_topic_manual_mode'])) {
            $payload = [
                'school_id' => 0,
                'subject_id' => 0,
                'levels' => [0],
            ];
            // $data['formData']['topic_id'] = $this->getTopicIDAll($data['topic'], $payload);
        }

        $comprehension_list = [];
        $comprehension_delete_list = [];
        if (!isset($data['question_test_id'])) {
            try {
                $data['formData']['session_id'] = $session->id;
                $answers = $data['formData']['answers'];
                if (isset($data['formData']['comprehension_list'])) {
                    $comprehension_list = $data['formData']['comprehension_list'];
                    unset($data['formData']['comprehension_list']);
                    if (isset($data['formData']['comprehension_delete_list'])) {
                        $comprehension_delete_list = $data['formData']['comprehension_delete_list'];
                        unset($data['formData']['comprehension_delete_list']);
                    }
                }
                unset($data['formData']['answers']);
                unset($data['formData']['school_id']);
                if (isset($data['update_id'])) {
                    $is_update = true;
                    $question = (new CbtQuestion)
                        ->select('id')
                        ->find($data['update_id']);
                    if (is_null($question))
                        return response()->json(['error' => 'Question data not found in system']);
                    unset($data['update_id']);

                    if (isset($data['in_use']) && $data['in_use']) {
                        $question->update(['tags' => $data['formData']['tags'], 'is_exam' => $data['formData']['is_exam']]);
                        $question->save();
                    } else {
                        $exists = (new CbtQuestion)
                            ->exists($data['formData']);
                        if ($exists->count() > 0 && $exists->id != $question->id) {
                            return response()->json(['error' => 'Another instance of this Question already exists in system']);
                        }
                        $question->update($data['formData']);
                        $question->save();
                        (new CbtAnswer)
                            ->where('question_id', $question->id)
                            ->delete();
                        if ($question->option_type_id != 3 &&
                            $question->option_type_id != 4
                        ) {
                            $payld = [
                                'question_id' => $question->id
                            ];
                            $anss = [];
                            foreach ($answers as $answer) {
                                $payld['is_correct'] = $answer['is_correct'];
                                $payld['value'] = $answer['value'];
                                $anss[] = $payld;
                            }
                            (new CbtAnswer)->insert($anss);
                        }
                        if ($question->option_type_id === 4) {
                            foreach ($comprehension_list as $ques) {
                                if (isset($ques['id'])) {
                                    $question_sub = (new CbtSubQuestion)
                                        ->find($ques['id']);
                                    $question_sub->update([
                                        'question_id' => $question->id,
                                        'option_type_id' => $ques['option_type_id'],
                                        'is_editor' => $ques['is_editor'],
                                        'question' => $ques['question'],
                                        'question_plain' => $ques['question_plain'],
                                    ]);
                                } else {
                                    $question_sub = (new CbtSubQuestion)
                                        ->create([
                                            'question_id' => $question->id,
                                            'option_type_id' => $ques['option_type_id'],
                                            'is_editor' => $ques['is_editor'],
                                            'question' => $ques['question'],
                                            'question_plain' => $ques['question_plain'],
                                        ]);
                                }
                                (new CbtSubAnswer)
                                    ->where('question_id', $question_sub->id)
                                    ->delete();
                                $payld = [
                                    'question_id' => $question_sub->id
                                ];
                                $anss = [];
                                foreach ($ques['answers'] as $answer) {
                                    $payld['is_correct'] = $answer['is_correct'];
                                    $payld['value'] = $answer['value'];
                                    $payld['created_at'] = DB::raw('CURRENT_TIMESTAMP');
                                    $payld['updated_at'] = DB::raw('CURRENT_TIMESTAMP');
                                    $anss[] = $payld;
                                }
                                (new CbtSubAnswer)
                                    ->insert($anss);
                            }
                        }

                        if (count($comprehension_delete_list) > 0) {
                            (new CbtSubAnswer)
                                ->whereIn('question_id', $comprehension_delete_list)
                                ->delete();
                            (new CbtSubQuestion)
                                ->destroy($comprehension_delete_list);

                        }

                    }
                } else {
                    $exists = (new CbtQuestion)
                        ->exists($data['formData']);
                    if ($exists->count() > 0) {
                        return response()->json(['error' => 'Another instance of this Question already exists in system']);
                    }
                    $question = (new CbtQuestion)->create($data['formData']);
                    if ($question->option_type_id != 3 &&
                        $question->option_type_id != 4
                    ) {
                        $payld = [
                            'question_id' => $question->id
                        ];
                        $anss = [];
                        foreach ($answers as $answer) {
                            $payld['is_correct'] = $answer['is_correct'];
                            $payld['value'] = $answer['value'];
                            $payld['created_at'] = DB::raw('CURRENT_TIMESTAMP');
                            $payld['updated_at'] = DB::raw('CURRENT_TIMESTAMP');
                            $anss[] = $payld;
                        }
                        (new CbtAnswer)
                            ->insert($anss);
                    }
                    if ($question->option_type_id === 4) {
                        foreach ($comprehension_list as $ques) {
                            $question_sub = (new CbtSubQuestion)
                                ->create([
                                    'question_id' => $question->id,
                                    'option_type_id' => $ques['option_type_id'],
                                    'is_editor' => $ques['is_editor'],
                                    'question' => $ques['question'],
                                    'question_plain' => $ques['question_plain'],
                                ]);
                            $payld = [
                                'question_id' => $question_sub->id
                            ];
                            $anss = [];
                            foreach ($ques['answers'] as $answer) {
                                $payld['is_correct'] = $answer['is_correct'];
                                $payld['value'] = $answer['value'];
                                $payld['created_at'] = DB::raw('CURRENT_TIMESTAMP');
                                $payld['updated_at'] = DB::raw('CURRENT_TIMESTAMP');
                                $anss[] = $payld;
                            }
                            (new CbtSubAnswer)
                                ->insert($anss);
                        }
                    }
                }
                $question = (new CbtQuestion)->with([
                    'optionType' => function ($q) {
                        $q->select('id', 'title', 'description');
                    }, 'optionAnswerType' => function ($q) {
                        $q->select('id', 'title', 'description');
                    }, 'difficulty' => function ($q) {
                        $q->select('id', 'title');
                    }, 'answers' => function ($q) {
                        $q->select('id', 'question_id', 'is_correct', 'value');
                    }, 'topic' => function ($q) {
                        $q->select('id', 'name');
                    }, 'subQuestion' => function ($q) {
                        $q->with(['answers']);
                    }
                ])->find($question->id);
                $question->no_of_questions = $question->subQuestion->count();
            } catch (\Exception $e) {
                $msg = $is_update ? "Update" : "Creation";
                // CbtService::logMsg($e->getMessage());
                return json_encode(['error' => "Question $msg Failed"], 200);
            }
        } 
        // else {
        //     $is_update = true;
        //     $question = (new CbtQuestionTest)
        //         ->select('id', 'question_id', 'test_id')
        //         ->with(['test' => function ($q) {
        //             $q->select('id', 'is_published');
        //         }])
        //         ->find($data['question_test_id']);
        //     if (is_null($question))
        //         return json_encode(['error' => 'Question not found'], 200);
        //     try {
        //         if (isset($data['formData']['comprehension_list'])) {
        //             $comprehension_list = $data['formData']['comprehension_list'];
        //             unset($data['formData']['comprehension_list']);
        //             if (isset($data['formData']['comprehension_delete_list'])) {
        //                 $comprehension_delete_list = $data['formData']['comprehension_delete_list'];
        //                 unset($data['formData']['comprehension_delete_list']);
        //             }
        //         }
        //         $data['formData']['session_id'] = $session->id;
        //         $answers = $data['formData']['answers'];
        //         unset($data['formData']['answers']);
        //         unset($data['formData']['school_id']);
        //         unset($data['formData']['subject_id']);
        //         $question->update($data['formData']);
        //         $question->save();
        //         if ($question->test['is_published'] == 0) {
        //             (new CbtAllClonedAnswer)
        //                 ->where('question_id', $question->id)
        //                 ->delete();
        //         }
        //         if ($question->option_type_id != 3 &&
        //             $question->option_type_id != 4
        //         ) {
        //             $payld = [
        //                 'question_id' => $question->id
        //             ];
        //             $anss = [];
        //             foreach ($answers as $answer) {
        //                 $payld['is_correct'] = $answer['is_correct'];
        //                 $payld['value'] = $answer['value'];
        //                 if ($question->test['is_published'] == 0) {
        //                     $anss[] = $payld;
        //                 } else {
        //                     (new CbtAllClonedAnswer)->find($answer['id'])
        //                         ->update($payld);
        //                 }
        //             }
        //             if ($question->test['is_published'] == 0) {
        //                 (new CbtAllClonedAnswer)->insert($anss);
        //             }
        //         }
        //         if ($question->option_type_id === 4) {
        //             foreach ($comprehension_list as $ques) {
        //                 if (isset($ques['id'])) {
        //                     $question_sub = (new CbtAllSubClonedQuestion)
        //                         ->find($ques['id']);
        //                     $question_sub->update([
        //                         'option_type_id' => $ques['option_type_id'],
        //                         'is_editor' => $ques['is_editor'],
        //                         'question' => $ques['question'],
        //                         'question_plain' => $ques['question_plain'],
        //                     ]);
        //                 } else {
        //                     $question_sub = (new CbtAllSubClonedQuestion)
        //                         ->create([
        //                             'question_test_id' => $question->id,
        //                             'question_id' => $question->question_id,
        //                             'option_type_id' => $ques['option_type_id'],
        //                             'is_editor' => $ques['is_editor'],
        //                             'question' => $ques['question'],
        //                             'question_plain' => $ques['question_plain'],
        //                         ]);
        //                 }
        //                 if ($question->test['is_published'] == 0) {
        //                     (new CbtAllSubClonedAnswer)
        //                         ->where('question_id', $question_sub->id)
        //                         ->delete();
        //                 }
        //                 $payld = [
        //                     'question_id' => $question_sub->id
        //                 ];
        //                 $anss = [];
        //                 foreach ($ques['answers'] as $answer) {
        //                     $payld['is_correct'] = $answer['is_correct'];
        //                     $payld['value'] = $answer['value'];
        //                     if ($question->test['is_published'] == 0) {
        //                         $payld['created_at'] = DB::raw('CURRENT_TIMESTAMP');
        //                         $payld['updated_at'] = DB::raw('CURRENT_TIMESTAMP');
        //                         $anss[] = $payld;
        //                         unset($payld['created_at']);
        //                         unset($payld['updated_at']);
        //                     } else {
        //                         (new CbtAllSubClonedAnswer)->find($answer['id'])
        //                             ->update($payld);
        //                     }
        //                 }
        //                 if ($question->test['is_published'] == 0) {
        //                     (new CbtAllSubClonedAnswer)
        //                         ->insert($anss);
        //                 }
        //             }
        //             if (count($comprehension_delete_list) > 0) {
        //                 (new CbtAllSubClonedAnswer)
        //                     ->whereIn('question_id', $comprehension_delete_list)
        //                     ->delete();
        //                 (new CbtAllSubClonedQuestion)
        //                     ->destroy($comprehension_delete_list);

        //             }
        //         }
        //         $q = (new CbtAllQuestionTest)->with([
        //             'optionType' => function ($q) {
        //                 $q->select('id', 'title', 'description');
        //             }, 'optionAnswerType' => function ($q) {
        //                 $q->select('id', 'title', 'description');
        //             }, 'difficulty' => function ($q) {
        //                 $q->select('id', 'title');
        //             }, 'answers' => function ($q) {
        //                 $q->select('id', 'question_id', 'is_correct', 'value');
        //             }, 'topic' => function ($q) {
        //                 $q->select('id', 'name');
        //             },
        //             'user' => function ($q) {
        //                 $q->select('id', 'full_name');
        //             }
        //             , 'level' => function ($q) {
        //                 $q->select('id', 'name');
        //             }, 'subClonedQuestion' => function ($q) {
        //                 $q->with(['answers']);
        //             }, 'test' => function ($q) {
        //                 $q->select('id', 'is_published');
        //             }
        //         ])
        //             ->select('id', 'test_id', 'question', 'mark', 'question_plain',
        //                 'position_id', 'difficulty_id', 'topic_id', 'option_type_id', 'option_answer_type_id', 'question_id', 'user_id', 'level_id', 'tags')
        //             ->find($question->id);
        //         $q->no_of_questions = $q->subClonedQuestion->count();
        //         $main = $q->toArray();
        //         $main['question'] = str_replace('<p>&nbsp;</p>', '', $main['question']);


        //         $main['custom_mark'] = $q->mark;
        //         $main['is_added'] = true;
        //         $question = (object)$main;
        //         if ($question->test['is_published'] == 1) {
        //             $this->afterPublishedUpdate(
        //                 $question,
        //                 $user, $request->ip(),
        //                 $request->server('HTTP_USER_AGENT'));
        //         }

        //         $main['question_test_id'] = $main['id'];
        //         $main['id'] = $main['question_id'];
        //     } catch (\Exception $e) {
        //         // dd($e);
        //         $msg = "Update";
        //         CbtService::logMsg($e->getMessage());
        //         return json_encode([
        //             'error' => "Question $msg Failed"
        //         ], 200);
        //     }

        // }

        $msg = $is_update ? "updated" : "created";
        return json_encode(['success' => "Question was $msg Successfully", 'question' => $question]);
    }
}
