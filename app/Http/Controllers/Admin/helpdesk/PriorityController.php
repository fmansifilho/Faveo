<?php

namespace App\Http\Controllers\Admin\helpdesk;

// controllers
use App\Http\Controllers\Common\NotificationController;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Common\FileuploadController;
use App\Http\Controllers\Controller;
// requests
use App\Http\Requests\helpdesk\CreateTicketRequest;
use App\Http\Requests\helpdesk\TicketRequest;
use App\Http\Requests\helpdesk\PriorityRequest;
// models
use App\Model\helpdesk\Agent\Teams;
use App\Model\helpdesk\Email\Emails;
use App\Model\helpdesk\Agent\Department;
use App\Model\helpdesk\Form\Fields;
use App\Model\helpdesk\Manage\Help_topic;
use App\Model\helpdesk\Manage\Sla_plan;
use App\Model\helpdesk\Settings\CommonSettings;
use App\Model\helpdesk\Notification\Notification;
use App\Model\helpdesk\Notification\UserNotification;
use App\Model\helpdesk\Settings\Alert;
use App\Model\helpdesk\Settings\Company;
use App\Model\helpdesk\Settings\Email;
use App\Model\helpdesk\Settings\System;
use App\Model\helpdesk\Ticket\Ticket_attachments;
use App\Model\helpdesk\Ticket\Ticket_Collaborator;
use App\Model\helpdesk\Ticket\Ticket_Form_Data;
use App\Model\helpdesk\Ticket\Ticket_Priority;
use App\Model\helpdesk\Ticket\Ticket_source;
use App\Model\helpdesk\Ticket\Ticket_Status;
use App\Model\helpdesk\Ticket\Ticket_Thread;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\helpdesk\Utility\CountryCode;
use App\Model\helpdesk\Utility\Date_time_format;
use App\Model\helpdesk\Utility\Timezones;
use App\User;
use Auth;
use DB;
use Exception;
use ForceUTF8\Encoding;
use GeoIP;
// classes
use Hash;
use Illuminate\Http\Request;
use Illuminate\support\Collection;
use Input;
use Lang;
use Mail;
use PDF;
use UTC;

/**
 * TicketController.
 *
 * @author      Ladybird <info@ladybirdweb.com>
 */
class PriorityController extends Controller {

    public function __construct(PhpMailController $PhpMailController, NotificationController $NotificationController) {
        $this->PhpMailController = $PhpMailController;
        $this->NotificationController = $NotificationController;
        $this->middleware('auth');
    }

    /**
     * Show the Inbox ticket list page.
     *
     * @return type response
     */
    public function priorityIndex() {
        $user_status=CommonSettings::where('id','=',6)->first();
        // dd( $user_status);
        
       return view('themes.default1.admin.helpdesk.manage.ticket_priority.index', compact('user_status'));
    }
       /**
     * Show the Inbox ticket list page.
     *
     * @return type response
     */
    public function userPriorityIndex(Request $request) {
          try {
        $user_status= $request->user_settings_priority;
        
        CommonSettings::where('id','=',6)->update(['status' => $user_status]);

       return 'Your Status Updated';
         } catch (Exception $e) {
            return Redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * 
     * @return type
     */
    public function priorityIndex1() {
        try {
            $ticket = new Ticket_Priority();
            $tickets = $ticket->select('priority_id', 'priority', 'priority_desc', 'priority_color', 'status', 'is_default', 'ispublic')->get();

            return \Datatable::Collection($tickets)
                            ->showColumns('priority', 'priority_desc')
                            ->addColumn('priority_color', function($model) {
                                return "<button class='btn' style = 'background-color:$model->priority_color'></button>";
                            })
                            ->addColumn('status', function($model) {
                                if ($model->status == 1) {
                                    return "<a style='color:green'>active</a>";
                                } elseif ($model->status == 0) {
                                    Ticket_Priority::where('priority_id', '=', '$priority_id')
                                    ->update(['priority_id' => '']);
                                    return "<a style='color:red'>inactive</a>";
                                }
                            })
                            ->addColumn('action', function($model) {
                                if ($model->is_default > 0) {
                                    return "<a href=" . url('ticket_priority/' . $model->priority_id . '/edit') . " class='btn btn-info btn-xs' disabled='disabled'>Edit</a>&nbsp;<a href=" . url('ticket_priority/' . $model->priority_id . '/destroy') . " class='btn btn-warning btn-info btn-xs' disabled='disabled' > delete </a>";
                                } else {
                                    return "<a href=" . url('ticket_priority/' . $model->priority_id . '/edit') . " class='btn btn-info btn-xs'>Edit</a>&nbsp;<a class='btn btn-danger btn-xs' onclick='confirmDelete(" . $model->priority_id . ")'>Delete </a>";
                                }
                            })
                            ->searchColumns('priority')
                            ->orderColumns('priority', 'priority_color')
                            ->make();
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * 
     * @return type
     */
    public function priorityCreate() {
        return view('themes.default1.admin.helpdesk.manage.ticket_priority.create');
    }

    public function priorityCreate1(PriorityRequest $request) {
        $tk_priority = new Ticket_Priority;
        $tk_priority->priority = $request->priority;
        $tk_priority->status = $request->status;
        $tk_priority->priority_desc = $request->priority_desc;
        $tk_priority->priority_color = $request->priority_color;
        $tk_priority->ispublic = $request->ispublic;
        $tk_priority->save();
        return \Redirect::route('priority.index')->with('success', Lang::get('lang.priority_successfully_created'));
    }

    /**
     * 
     * @param type $priority_id
     * @return type
     */
    public function priorityEdit($priority_id) {
        
       $tk_priority = Ticket_Priority::wherepriority_id($priority_id)->first();
      
        return view('themes.default1.admin.helpdesk.manage.ticket_priority.edit', compact('tk_priority'));
    }

    /**
     * 
     * @param PriorityRequest $request
     * @return type
     */
    public function priorityEdit1(PriorityRequest $request) {
        $priority_id = $request->priority_id;
        $tk_priority = Ticket_Priority::findOrFail($priority_id);
        $tk_priority->priority = $request->priority;
        $tk_priority->status = $request->status;
        $tk_priority->priority_desc = $request->priority_desc;
        $tk_priority->priority_color = $request->priority_color;
        $tk_priority->ispublic = $request->ispublic;
        $tk_priority->save();
        if ($request->input('default_priority') == 'on') {
            Ticket_Priority::where('is_default', '>', '0')
                    ->update(['is_default' => '0']);
            Ticket_Priority::where('priority_id', '=', $priority_id)
                    ->update(['is_default' => $priority_id]);
        }
        return \Redirect::route('priority.index')->with('success', (Lang::get('lang.priority_successfully_updated')));
    }

    /**
     * 
     * @param type $priority_id
     * @return type
     */
    public function destroy($priority_id) {

        $default_priority = Ticket_Priority::where('is_default', '>', '0')->first();
// dd($default_priority->is_default);
        $topic = DB::table('help_topic')->where('priority', '=', $priority_id)->update(['priority' => $default_priority->is_default]);
        // if ($topic > 0) {
        //     if ($topic > 1) {
        //         $text_topic = 'Emails';
        //     } else {
        //         $text_topic = 'Email';
        //     }
        //     $topic = '<li>'.Lang::get('lang.associated_help_topic_have_been_moved_to_default_sla').'</li>';
        // } else {
        //     $topic = '';
        // }
        // dd('llll');
        $tk_priority = Ticket_Priority::findOrFail($priority_id);

        $tk_priority->delete();

        return \Redirect::route('priority.index')->with('success', (Lang::get('lang.delete_successfully')));
    }

}
