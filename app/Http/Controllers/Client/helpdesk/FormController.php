<?php

namespace App\Http\Controllers\Client\helpdesk;

// controllers
use App\Http\Controllers\Agent\helpdesk\TicketWorkflowController;
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\ClientRequest;
use App\Model\helpdesk\Agent\Department;
// models
use App\Model\helpdesk\Form\Fields;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Settings\Ticket;
use App\Model\helpdesk\Ticket\Ticket_attachments;
use App\Model\helpdesk\Ticket\Ticket_source;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Utility\CountryCode;
use App\User;
use Exception;
// classes
use Form;
use GeoIP;
use Illuminate\Http\Request;
use Input;
use Lang;
use Redirect;

/**
 * FormController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class FormController extends Controller {

    /**
     * Create a new controller instance.
     * Constructor to check.
     *
     * @return void
     */
    public function __construct(TicketWorkflowController $TicketWorkflowController) {
        $this->middleware('board');
        // creating a TicketController instance
        $this->TicketWorkflowController = $TicketWorkflowController;
    }

    /**
     * getform.
     *
     * @param type Help_topic $topic
     *
     * @return type
     */
    public function getForm(Help_topic $topic, CountryCode $code) {
        if (\Config::get('database.install') == '%0%') {
            return \Redirect::route('license');
        }
        $location = GeoIP::getLocation('');
        $phonecode = $code->where('iso', '=', $location['isoCode'])->first();
        if (System::first()->status == 1) {
            $topics = $topic->get();
            $codes = $code->get();
//             dd($phonecode);
            if ($phonecode->phonecode) {
                $phonecode = $phonecode->phonecode;
            } else {
                $phonecode = "";
            }
            return view('themes.default1.client.helpdesk.form', compact('topics', 'codes'))->with('phonecode', $phonecode);
        } else {
            return \Redirect::route('home');
        }
    }

    /**
     * This Function to post the form for the ticket.
     *
     * @param type Form_name    $name
     * @param type Form_details $details
     *
     * @return type string
     */
    public function postForm($id, Help_topic $topic) {
        if ($id != 0) {
            $helptopic = $topic->where('id', '=', $id)->first();
            $custom_form = $helptopic->custom_form;
            $values = Fields::where('forms_id', '=', $custom_form)->get();
            if (!$values) {
                
            }
            if ($values) {
                foreach ($values as $form_data) {
                    if ($form_data->type == "select") {
                        $form_fields = explode(',', $form_data->value);
                        $var = "";
                        foreach ($form_fields as $form_field) {
                            $var .= '<option value="' . $form_field . '">' . $form_field . '</option>';
                        }
                        echo '<br/><label>' . ucfirst($form_data->label) . '</label><select class="form-control" name="' . $form_data->name . '">' . $var . '</select>';
                    } elseif ($form_data->type == "radio") {
                        $type2 = $form_data->value;
                        $vals = explode(',', $type2);
                        echo '<br/><label>' . ucfirst($form_data->label) . '</label><br/>';
                        foreach ($vals as $val) {
                            echo '<input type="' . $form_data->type . '" name="' . $form_data->name . '"> ' . $form_data->value . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                        echo "<br/>";
                    } elseif ($form_data->type == "textarea") {
                        $type3 = $form_data->value;
                        echo '<br/><label>' . $form_data->label . '</label></br><textarea id="unique-textarea" name="' . $form_data->name . '" class="form-control" style="height:15%;"></textarea>';
                    } elseif ($form_data->type == "checkbox") {
                        $type4 = $form_data->value;
                        $checks = explode(',', $type4);
                        echo '<br/><label>' . ucfirst($form_data->label) . '</label><br/>';
                        foreach ($checks as $check) {
                            echo '<input type="' . $form_data->type . '" name="' . $form_data->name . '">&nbsp&nbsp' . $check;
                        }
                    } else {
                        echo '<br/><label>' . ucfirst($form_data->label) . '</label><input type="' . $form_data->type . '" class="form-control"   name="' . $form_data->name . '" />';
                    }
                }
                echo '<br/><br/>';
            }
        } else {
            return;
        }
    }

    /**
     * Posted form.
     *
     * @param type Request $request
     * @param type User    $user
     */
    public function postedForm(User $user, ClientRequest $request, Ticket $ticket_settings, Ticket_source $ticket_source, Ticket_attachments $ta, CountryCode $code) {
        $form_extras = $request->except('Name', 'Phone', 'Email', 'Subject', 'Details', 'helptopic', '_wysihtml5_mode', '_token');

        $name = $request->input('Name');
        $phone = $request->input('Phone');
        $email = $request->input('Email');
        $subject = $request->input('Subject');
        $details = $request->input('Details');
        $phonecode = $request->input('Code');
        $System = System::where('id', '=', 1)->first();
        $departments = Department::where('id', '=', $System->department)->first();
        $department = $departments->id;
        $mobile_number = $request->input('Mobile');
        $status = $ticket_settings->first()->status;
        $helptopic = $request->input('helptopic');
        $sla = $ticket_settings->first()->sla;
        $priority = $ticket_settings->first()->priority;
        $source = $ticket_source->where('name', '=', 'web')->first()->id;
        $attachments = $request->file('attachment');
        $collaborator = null;
        $assignto = null;
        $auto_response = 0;
        $team_assign = null;
        if ($phone != null || $mobile_number != null) {
            $location = GeoIP::getLocation();
            $geoipcode = $code->where('iso', '=', $location['isoCode'])->first();
            if ($phonecode == null) {
                $data = [
                    'fails' => Lang::get('lang.country-code-required-error'),
                    'phonecode' => $geoipcode->phonecode,
                    'country_code_error' => 1,
                ];

                return Redirect::back()->with($data)->withInput($request->except('password'));
            } else {
                $code = CountryCode::select('phonecode')->where('phonecode', '=', $phonecode)->get();
                if (!count($code)) {
                    $data = [
                        'fails' => Lang::get('lang.incorrect-country-code-error'),
                        'phonecode' => $geoipcode->phonecode,
                        'country_code_error' => 1,
                    ];

                    return Redirect::back()->with($data)->withInput($request->except('password'));
                }
            }
        }
        $result = $this->TicketWorkflowController->workflow($email, $name, $subject, $details, $phone, $phonecode, $mobile_number, $helptopic, $sla, $priority, $source, $collaborator, $department, $assignto, $team_assign, $status, $form_extras, $auto_response);
        
        if ($result[1] == 1) {
            $ticketId = Tickets::where('ticket_number', '=', $result[0])->first();
            $thread = Ticket_Thread::where('ticket_id', '=', $ticketId->id)->first();
            if ($attachments != null) {
                foreach ($attachments as $attachment) {
                    if ($attachment != null) {
                        $name = $attachment->getClientOriginalName();
                        $type = $attachment->getClientOriginalExtension();
                        $size = $attachment->getSize();
                        $data = file_get_contents($attachment->getRealPath());
                        $attachPath = $attachment->getRealPath();
                        $ta->create(['thread_id' => $thread->id, 'name' => $name, 'size' => $size, 'type' => $type, 'file' => $data, 'poster' => 'ATTACHMENT']);
                    }
                }
            }
            // dd($result);
            return Redirect::back()->with('success', Lang::get('lang.Ticket-has-been-created-successfully-your-ticket-number-is') . ' ' . $result[0] . '. ' . Lang::get('lang.Please-save-this-for-future-reference'));
        }
        dd($result);
    }

    /**
     * reply.
     *
     * @param type $value
     *
     * @return type view
     */
    public function post_ticket_reply($id, Request $request) {
        try {
            if ($comment != null) {
                $tickets = Tickets::where('id', '=', $id)->first();
                $thread = Ticket_Thread::where('ticket_id', '=', $tickets->id)->first();

                $subject = $thread->title . '[#' . $tickets->ticket_number . ']';
                $body = $request->input('comment');

                $user_cred = User::where('id', '=', $tickets->user_id)->first();

                $fromaddress = $user_cred->email;
                $fromname = $user_cred->user_name;
                $phone = '';
                $phonecode = '';
                $mobile_number = '';

                $helptopic = $tickets->help_topic_id;
                $sla = $tickets->sla;
                $priority = $tickets->priority_id;
                $source = $tickets->source;
                $collaborator = '';
                $dept = $tickets->dept_id;
                $assign = $tickets->assigned_to;
                $form_data = null;
                $team_assign = null;
                $ticket_status = null;
                $auto_response = 0;

                $this->TicketWorkflowController->workflow($fromaddress, $fromname, $subject, $body, $phone, $phonecode, $mobile_number, $helptopic, $sla, $priority, $source, $collaborator, $dept, $assign, $team_assign, $ticket_status, $form_data, $auto_response);

                return \Redirect::back()->with('success1', Lang::get('lang.successfully_replied'));
            } else {
                return \Redirect::back()->with('fails1', Lang::get('lang.please_fill_some_data'));
            }
        } catch (Exception $e) {
            return \Redirect::back()->with('fails1', $e->getMessage());
        }
//
//        $comment = $request->input('comment');
//        if ($comment != null) {
//            $tickets = Tickets::where('id', '=', $id)->first();
//            $threads = new Ticket_Thread();
//            $tickets->closed_at = null;
//            $tickets->closed = 0;
//            $tickets->reopened_at = date('Y-m-d H:i:s');
//            $tickets->reopened = 1;
//            $threads->user_id = $tickets->user_id;
//            $threads->ticket_id = $tickets->id;
//            $threads->poster = 'client';
//            $threads->body = $comment;
//            try {
//                $threads->save();
//                $tickets->save();
//
//                return \Redirect::back()->with('success1', Lang::get('lang.successfully_replied'));
//            } catch (Exception $e) {
//                return \Redirect::back()->with('fails1', $e->getMessage());
//            }
//        } else {
//            return \Redirect::back()->with('fails1', Lang::get('lang.please_fill_some_data'));
//        }
    }

}
