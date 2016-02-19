<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\helpdesk\TicketRequest;
//use Illuminate\Support\Facades\Request as Value;
use App\Model\helpdesk\Agent\Department;
use App\Model\helpdesk\Agent\Teams;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Manage\Sla_plan;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Ticket\Ticket_attachments;
use App\Model\helpdesk\Ticket\Ticket_source;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Utility\Priority;
use App\User;
use Exception;
use Illuminate\Http\Request;

/**
 * -----------------------------------------------------------------------------
 * Api Controller
 * -----------------------------------------------------------------------------.
 * 
 * 
 * @author Vijay Sebastian <vijay.sebastian@ladybirdweb.com>
 * @copyright (c) 2016, Ladybird Web Solution
 * @name Faveo HELPDESK
 *
 * @version v1
 */
class ApiController extends Controller
{
    public $user;
    public $request;
    public $ticket;
    public $model;
    public $thread;
    public $attach;
    public $ticketRequest;
    public $faveoUser;
    public $team;
    public $setting;
    public $helptopic;
    public $slaPlan;
    public $department;
    public $priority;
    public $source;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->middleware('jwt.auth');
        $this->middleware('api', ['except' => 'GenerateApiKey']);

        $user = \JWTAuth::parseToken()->authenticate();
        $this->user = $user;

        $ticket = new TicketController();
        $this->ticket = $ticket;

        $model = new Tickets();
        $this->model = $model;

        $thread = new Ticket_Thread();
        $this->thread = $thread;

        $attach = new Ticket_attachments();
        $this->attach = $attach;

        $ticketRequest = new TicketRequest();
        $this->ticketRequest = $ticketRequest;

        $faveoUser = new User();
        $this->faveoUser = $faveoUser;

        $team = new Teams();
        $this->team = $team;

        $setting = new System();
        $this->setting = $setting;

        $helptopic = new Help_topic();
        $this->helptopic = $helptopic;

        $slaPlan = new Sla_plan();
        $this->slaPlan = $slaPlan;

        $priority = new Priority();
        $this->priority = $priority;

        $department = new Department();
        $this->department = $department;

        $source = new Ticket_source();
        $this->source = $source;
    }

    /**
     * Create Tickets.
     *
     * @method POST
     *
     * @param user_id,subject,body,helptopic,sla,priority,dept
     *
     * @return json
     */
    public function createTicket()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'user_id'   => 'required|exists:users,id',
                        'subject'   => 'required',
                        'body'      => 'required',
                        'helptopic' => 'required|exists:help_topic,id',
                        'sla'       => 'required|exists:sla_plan,id',
                        'priority'  => 'required|exists:ticket_priority,priority_id',
                        'dept'      => 'required|exists:department,id',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }

            $user_id = $this->request->input('user_id');

            $subject = $this->request->input('subject');
            $body = $this->request->input('body');
            $helptopic = $this->request->input('helptopic');
            $sla = $this->request->input('sla');
            $priority = $this->request->input('priority');
            $headers = $this->request->input('headers');
            $dept = $this->request->input('dept');

            $assignto = $this->request->input('assignto');
            $form_data = $this->request->input('form_data');
            $source = $this->request->input('source');
            $attach = $this->request->input('attachments');
            /*
             * return s ticket number
             */
            $response = $this->ticket->createTicket($user_id, $subject, $body, $helptopic, $sla, $priority, $source, $headers, $dept, $assignto, $form_data, $attach);

            /*
             * return ticket details
             */
            //dd($response);
            $result = $this->thread->where('id', $response)->first();
            //$result = $this->attach($result->id,$file);
            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Reply for the ticket.
     *
     * @param TicketRequest $request
     *
     * @return json
     */
    public function ticketReply()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'ticket_ID'     => 'required|exists:tickets,id',
                        'reply_content' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $attach = $this->request->input('attachments');
            $result = $this->ticket->reply($this->thread, $this->request, $this->attach, $attach);

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Edit a ticket.
     *
     * @return json
     */
    public function editTicket()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'ticket_id'       => 'required|exists:tickets,id',
                        'subject'         => 'required',
                        'sla_plan'        => 'required|exists:sla_plan,id',
                        'help_topic'      => 'required|exists:help_topic,id',
                        'ticket_source'   => 'required|exists:ticket_source,id',
                        'ticket_priority' => 'required|exists:ticket_priority,priority_id',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $ticket_id = $this->request->input('ticket_id');
            $result = $this->ticket->ticketEditPost($ticket_id, $this->thread, $this->model);

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Delete The Ticket.
     *
     * @return json
     */
    public function deleteTicket()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'ticket_id' => 'required|exists:tickets,id',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('ticket_id');

            $result = $this->ticket->delete($id, $this->model);

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get all opened tickets.
     *
     * @return json
     */
    public function openedTickets()
    {
        try {
            //            $result = $this->model->where('status', '=', 1)->where('isanswered', '=', 0)->where('assigned_to', '=', null)->orderBy('id', 'DESC')->get();
//            return response()->json(compact('result'));

            $result = $this->user->join('tickets', function ($join) {
                        $join->on('users.id', '=', 'tickets.user_id')
                        ->where('isanswered', '=', 0)->where('status', '=', 1)->whereNull('assigned_to');
                    })
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->join('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id')
                        ->whereNotNull('title');
                    })
                    ->select('first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'title', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toJson();

            return $result;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get Unsigned Tickets.
     *
     * @return json
     */
    public function unassignedTickets()
    {
        try {
            //dd('sdhjbc');
//            $result = $this->model->where('assigned_to', '=', null)->where('status', '1')->orderBy('id', 'DESC')->get();
//            return response()->json(compact('result'));
            $unassigned = $this->user->join('tickets', function ($join) {
                        $join->on('users.id', '=', 'tickets.user_id')
                        ->whereNull('assigned_to')->where('status', '=', 1);
                    })
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->join('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id')
                        ->whereNotNull('title');
                    })
                    ->select('first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'title', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toJson();

            return $unassigned;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get closed Tickets.
     *
     * @return json
     */
    public function closeTickets()
    {
        try {
            //            $result = $this->model->where('status', '>', 1)->where('status', '<', 4)->orderBy('id', 'DESC')->get();
//            return response()->json(compact('result'));

            $result = $this->user->join('tickets', function ($join) {
                        $join->on('users.id', '=', 'tickets.user_id')
                        ->where('isanswered', '=', 0)->where('status', '>', 1)->where('status', '<', 4);
                    })
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->join('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id')
                        ->whereNotNull('title');
                    })
                    ->select('first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'title', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toJson();

            return $result;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get All agents.
     *
     * @return json
     */
    public function getAgents()
    {
        try {
            $result = $this->faveoUser->where('role', 'agent')->orWhere('role', 'admin')->where('active', 1)->get();

            return response()->json(compact('result'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get All Teams.
     *
     * @return json
     */
    public function getTeams()
    {
        try {
            $result = $this->team->get();

            return response()->json(compact('result'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * To assign a ticket.
     *
     * @return json
     */
    public function assignTicket()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'ticket_id' => 'required',
                        'user'      => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('ticket_id');
            $response = $this->ticket->assign($id);
            if ($response == 1) {
                $result = 'success';

                return response()->json(compact('result'));
            } else {
                return response()->json(compact('response'));
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get all customers.
     *
     * @return json
     */
    public function getCustomers()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'search' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $search = $this->request->input('search');
            $result = $this->faveoUser->where('first_name', 'like', '%'.$search.'%')->orWhere('last_name', 'like', '%'.$search.'%')->orWhere('user_name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->get();

            return response()->json(compact('result'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get a customer by id.
     *
     * @return json
     */
    public function getCustomer()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'user_id' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('user_id');
            $result = $this->faveoUser->where('id', $id)->first();

            return response()->json(compact('result'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Search tickets.
     *
     * @return json
     */
    public function searchTicket()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'search' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $search = $this->request->input('search');
            $result = $this->thread->select('ticket_id')->where('title', 'like', '%'.$search.'%')->orWhere('body', 'like', '%'.$search.'%')->get();

            return response()->json(compact('result'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get threads of a ticket.
     *
     * @return json
     */
    public function ticketThreads()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'id' => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('id');
            $result = $this->thread->where('ticket_id', $id)->get();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Check the url is valid or not.
     *
     * @return json
     */
    public function checkUrl()
    {
        //dd($this->request);
        try {
            $v = \Validator::make($this->request->all(), [
                        'url' => 'required|url',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }

            $url = $this->request->input('url');
            $url = $url.'/api/v1/helpdesk/check-url?api_key='.$this->request->input('api_key').'&token='.\Config::get('app.token');
            $result = $this->CallGetApi($url);
            //dd($result);
            return response()->json(compact('result'));
        } catch (\Exception $ex) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Success for currect url.
     *
     * @return string
     */
    public function urlResult()
    {
        return 'success';
    }

    /**
     * Call curl function for Get Method.
     *
     * @param type $url
     *
     * @return type int|string|json
     */
    public function callGetApi($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'error:'.curl_error($curl);
        }

        return $response;
        curl_close($curl);
    }

    /**
     * Call curl function for POST Method.
     *
     * @param type $url
     * @param type $data
     *
     * @return type int|string|json
     */
    public function callPostApi($url, $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'error:'.curl_error($curl);
        }

        return $response;
        curl_close($curl);
    }

    /**
     * To generate api string.
     * 
     * @return type | json
     */
    public function generateApiKey()
    {
        try {
            $set = $this->setting->where('id', '1')->first();
            //dd($set);
            if ($set->api_enable == 1) {
                $key = str_random(32);
                $set->api_key = $key;
                $set->save();
                $result = $set->api_key;

                return response()->json(compact('result'));
            } else {
                $result = 'please enable api';

                return response()->json(compact('result'));
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get help topics.
     *
     * @return json
     */
    public function getHelpTopic()
    {
        try {
            $result = $this->helptopic->get();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get Sla plans.
     *
     * @return json
     */
    public function getSlaPlan()
    {
        try {
            $result = $this->slaPlan->get();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get priorities.
     *
     * @return json
     */
    public function getPriority()
    {
        try {
            $result = $this->priority->get();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Get departments.
     *
     * @return json
     */
    public function getDepartment()
    {
        try {
            $result = $this->department->get();

            return response()->json(compact('result'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Getting the tickets.
     *
     * @return type json
     */
    public function getTickets()
    {
        try {
            $tickets = $this->model->paginate(10);
            $tickets->toJson();

            return $tickets;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Fetching the Inbox details.
     *
     * @return type json
     */
    public function inbox()
    {
        try {
            $inbox = $this->user->join('tickets', 'users.id', '=', 'tickets.user_id')
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->join('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id')
                        ->whereNotNull('title');
                    })
                    ->select('first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'title', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toJson();

            return $inbox;
        } catch (\Exception $ex) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    /**
     * Create internal note.
     *
     * @return type json
     */
    public function internalNote()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'userid'   => 'required|exists:users,id',
                        'ticketid' => 'required|exists:tickets,id',
                        'body'     => 'required',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $userid = $this->request->input('userid');
            $ticketid = $this->request->input('ticketid');

            $body = $this->request->input('body');
            $thread = $this->thread->create(['ticket_id' => $ticketid, 'user_id' => $userid, 'is_internal' => 1, 'body' => $body]);

            return response()->json(compact('thread'));
        } catch (\Exception $ex) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function getTrash()
    {
        try {
            $trash = $this->user->join('tickets', function ($join) {
                        $join->on('users.id', '=', 'tickets.user_id')
                        ->where('status', '=', 5);
                    })
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->join('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id')
                        ->whereNotNull('title');
                    })
                    ->select('first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'title', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toJson();

            return $trash;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function getMyTickets()
    {
        try {
            $v = \Validator::make($this->request->all(), [
                        'user_id' => 'required|exists:users,id',
            ]);
            if ($v->fails()) {
                $error = $v->errors();

                return response()->json(compact('error'));
            }
            $id = $this->request->input('user_id');
            if ($this->user->where('id', $id)->first()->role == 'user') {
                $error = 'This user is not an Aget or Admin';

                return response()->json(compact('error'));
            }
            $result = $this->user->join('tickets', function ($join) use ($id) {
                        $join->on('users.id', '=', 'tickets.user_id')
                        ->where('user_id', '=', $id);
                    })
                    ->join('department', 'department.id', '=', 'tickets.dept_id')
                    ->join('ticket_priority', 'ticket_priority.priority_id', '=', 'tickets.priority_id')
                    ->join('sla_plan', 'sla_plan.id', '=', 'tickets.sla')
                    ->join('help_topic', 'help_topic.id', '=', 'tickets.help_topic_id')
                    ->join('ticket_status', 'ticket_status.id', '=', 'tickets.status')
                    ->join('ticket_thread', function ($join) {
                        $join->on('tickets.id', '=', 'ticket_thread.ticket_id')
                        ->whereNotNull('title');
                    })
                    ->select('first_name', 'last_name', 'email', 'profile_pic', 'ticket_number', 'tickets.id', 'title', 'tickets.created_at', 'department.name as department_name', 'ticket_priority.priority as priotity_name', 'sla_plan.name as sla_plan_name', 'help_topic.topic as help_topic_name', 'ticket_status.name as ticket_status_name')
                    ->groupby('tickets.id')
                    ->distinct()
                    ->paginate(10)
                    ->toJson();

            return $result;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $line = $e->getLine();
            $file = $e->getFile();

            return response()->json(compact('error', 'file', 'line'));
        } catch (\TokenExpiredException $e) {
            $error = $e->getMessage();

            return response()->json(compact('error'));
        }
    }
}
