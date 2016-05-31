<?php

namespace App\Http\Controllers\Client\helpdesk;

// controllers
use App\Http\Controllers\Agent\helpdesk\TicketWorkflowController;
use App\Http\Controllers\Common\SettingsController;
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
use Illuminate\Http\Request;
use Input;
use Mail;
use Redirect;
use GeoIP;
use Lang;

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
        // mail smtp settings
//        SettingsController::smtp();
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
<<<<<<< HEAD
    public function getForm(Help_topic $topic, CountryCode $code) {
=======
    public function getForm(Help_topic $topic, CountryCode $code)
    {
>>>>>>> origin/master
        if (\Config::get('database.install') == '%0%') {
            return \Redirect::route('license');
        }
        $location = GeoIP::getLocation('');
<<<<<<< HEAD
        $phonecode = $code->where('iso', '=', $location['isoCode'])->first();
        if (System::first()->status == 1) {
            $topics = $topic->get();
            $codes = $code->get();
            return view('themes.default1.client.helpdesk.form', compact('topics', 'codes'))->with('phonecode', $phonecode->phonecode);
=======
        $phonecode = $code->where('iso', '=' , $location['isoCode'])->first();
        if (System::first()->status == 1) {
            $topics = $topic->get();
            $codes  = $code->get();
            return view('themes.default1.client.helpdesk.form', compact('topics','codes'))->with('phonecode', $phonecode->phonecode);
>>>>>>> origin/master
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
        // dd($id);
        if ($id != 0) {
            $helptopic = $topic->where('id', '=', $id)->first();
            $custom_form = $helptopic->custom_form;
            $values = Fields::where('forms_id', '=', $custom_form)->get();
            if (!$values) {
                
            }
            if ($values) {
                foreach ($values as $value) {
                    if ($value->type == 'select') {
                        $data = $value->value;
                        $value = explode(',', $data);
                        echo '<select class="form-control">';
                        foreach ($value as $option) {
                            echo '<option>' . $option . '</option>';
                        }
                        echo '</select></br>';
                    } elseif ($value->type == 'radio') {
                        $type2 = $value->value;
                        $val = explode(',', $type2);
<<<<<<< HEAD
                        echo '<label class="radio-inline">' . $value->label . '</label>&nbsp&nbsp&nbsp<input type="' . $value->type . '" name="' . $value->name . '">&nbsp;&nbsp;' . $val[0] . '
                        &nbsp&nbsp&nbsp<input type="' . $value->type . '" name="' . $value->name . '">&nbsp;&nbsp;' . $val[1] . '</br>';
=======
                        echo '<label class="radio-inline">'.$value->label.'</label>&nbsp&nbsp&nbsp<input type="'.$value->type.'" name="'.$value->name.'">&nbsp;&nbsp;'.$val[0].'
                        &nbsp&nbsp&nbsp<input type="'.$value->type.'" name="'.$value->name.'">&nbsp;&nbsp;'.$val[1].'</br>';
>>>>>>> origin/master
                    } elseif ($value->type == 'textarea') {
                        $type3 = $value->value;
                        $v = explode(',', $type3);
                        //dd($v);
                        if (array_key_exists(1, $v)) {
                            echo '<label>' . $value->label . '</label></br><textarea class=form-control rows="' . $v[0] . '" cols="' . $v[1] . '"></textarea></br>';
                        } else {
                            echo '<label>' . $value->label . '</label></br><textarea class=form-control rows="10" cols="60"></textarea></br>';
                        }
                    } elseif ($value->type == 'checkbox') {
                        $type4 = $value->value;
                        $check = explode(',', $type4);
                        echo '<label class="radio-inline">' . $value->label . '&nbsp&nbsp&nbsp<input type="' . $value->type . '" name="' . $value->name . '">&nbsp&nbsp' . $check[0] . '</label><label class="radio-inline"><input type="' . $value->type . '" name="' . $value->name . '">&nbsp&nbsp' . $check[1] . '</label></br>';
                    } else {
                        echo '<label>' . $value->label . '</label><input type="' . $value->type . '" class="form-control"   name="' . $value->name . '" /></br>';
                    }
                }
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
<<<<<<< HEAD
    public function postedForm(User $user, ClientRequest $request, Ticket $ticket_settings, Ticket_source $ticket_source, Ticket_attachments $ta, CountryCode $code) {
=======
    public function postedForm(User $user, ClientRequest $request, Ticket $ticket_settings, Ticket_source $ticket_source, Ticket_attachments $ta, CountryCode $code)
    {
>>>>>>> origin/master
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
        $helptopic = $ticket_settings->first()->help_topic;
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
<<<<<<< HEAD
            $geoipcode = $code->where('iso', '=', $location['isoCode'])->first();
            if ($phonecode == null) {
                $data = array(
                    'fails' => Lang::get('lang.country-code-required-error'),
                    'phonecode' => $geoipcode->phonecode,
                );
=======
            $geoipcode = $code->where('iso', '=' , $location['isoCode'])->first();
            if ($phonecode == null) {
                    $data = array(
                        'fails'  => Lang::get('lang.country-code-required-error'),
                        'phonecode'   => $geoipcode->phonecode,
                        
                    );
>>>>>>> origin/master
                return Redirect::back()->with($data)->withInput($request->except('password'));
            } else {
                $code = CountryCode::select('phonecode')->where('phonecode', '=', $phonecode)->get();
                if (!count($code)) {
                    $data = array(
<<<<<<< HEAD
                        'fails' => Lang::get('lang.incorrect-country-code-error'),
                        'phonecode' => $geoipcode->phonecode,
                    );
                    return Redirect::back()->with($data)->withInput($request->except('password'));
                }
=======
                        'fails'  => Lang::get('lang.incorrect-country-code-error'),
                        'phonecode'   => $geoipcode->phonecode,
                        
                    );
                    return Redirect::back()->with($data)->withInput($request->except('password'));
                }     
>>>>>>> origin/master
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

<<<<<<< HEAD
            return Redirect::back()->with('success', 'Ticket has been created successfully, your ticket number is <b>' . $result[0] . '</b> Please save this for future reference.');
=======
            return Redirect::back()->with('success', 'Ticket has been created successfully, your ticket number is <b>'.$result[0].'</b> Please save this for future reference.');
>>>>>>> origin/master
        }
    }

    /**
     * reply.
     *
     * @param type $value
     *
     * @return type view
     */
    public function post_ticket_reply($id, Request $request) {
        $comment = $request->input('comment');
        if ($comment != null) {
            $tickets = Tickets::where('id', '=', $id)->first();
            $threads = new Ticket_Thread();
            $tickets->closed_at = null;
            $tickets->closed = 0;
            $tickets->reopened_at = date('Y-m-d H:i:s');
            $tickets->reopened = 1;
            $threads->user_id = $tickets->user_id;
            $threads->ticket_id = $tickets->id;
            $threads->poster = 'client';
            $threads->body = $comment;
            try {
                $threads->save();
                $tickets->save();

                return \Redirect::back()->with('success1', 'Successfully replied');
            } catch (Exception $e) {
                return \Redirect::back()->with('fails1', $e->errorInfo[2]);
            }
        } else {
            return \Redirect::back()->with('fails1', 'Please fill some data!');
        }
    }

}
