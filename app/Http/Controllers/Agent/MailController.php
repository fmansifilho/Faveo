<?php namespace App\Http\Controllers\Agent;
use App;
use DB;
use App\User;
use App\Upload;
use Artisan;
use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;
use \ForceUTF8\Encoding;
use App\Http\Controllers\Agent\TicketController;
use App\Http\Controllers\Controller;
use App\Model\Email\Emails;
use App\Model\Settings\Email;
use App\Model\Ticket\Ticket_attachments;
use App\Model\Ticket\Ticket_Thread;
use App\Model\Settings\System;
use Crypt;
use Schedule;
use File;

/**
 * MailController
 *
 * @package     Controllers
 * @subpackage  Controller
 * @author      Ladybird <info@ladybirdweb.com>
 */
class MailController extends Controller {

	/**
	 * constructor
	 * Create a new controller instance.
	 * @param type TicketController $TicketController
	 */
	public function __construct(TicketController $TicketController) {
		$this->TicketController = $TicketController;
	}

	/**
	 * Reademails
	 * @return type
	 */
	public function readmails(Emails $emails, Email $settings_email, System $system)
	{
		$path_url = $system->first()->url;
		if($settings_email->first()->email_fetching == 1)
		{
		if($settings_email->first()->all_emails == 1)
		{
		$helptopic = $this->TicketController->default_helptopic();
		$sla = $this->TicketController->default_sla();
		$email = $emails->get();
		foreach($email as $e_mail) 
		{
			$password = Crypt::decrypt($e_mail->password);
			$mailbox = new ImapMailbox($e_mail->imap_config, $e_mail->user_name, $password, __DIR__);
			$mails = array();
			$mailsIds = $mailbox->searchMailBox('SINCE '. date('d-M-Y', strtotime("-1 day")));
			if(!$mailsIds) {
			    die('Mailbox is empty');
			}
			// dd($mailsIds);
			foreach($mailsIds as $mailId)
			{
				$overview = $mailbox->get_overview($mailId);	
				$var = $overview[0]->seen ? 'read' : 'unread';
				if ($var == 'unread') {
					$mail = $mailbox->getMail($mailId);
					if($settings_email->email_collaborator == 1) {
						$collaborator = $mail->cc;
					} else {
						$collaborator = null;
					}
					$body = $mail->textHtml;
					// dd($mailId);
					if($body == null)
					{
						$body = $mailbox->backup_getmail($mailId);
						$body = str_replace('\r\n', '<br/>', $body);
						// var_dump($body);
					}
					// dd($body);
					$date = $mail->date; 
	     			$datetime = $overview[0]->date;
					$date_time = explode(" ", $datetime);
					$date = $date_time[1] . "-" . $date_time[2] . "-" . $date_time[3] . " " . $date_time[4];
					$date = date('Y-m-d H:i:s', strtotime($date));
					$subject = $mail->subject;
					$fromname = $mail->fromName;
					$fromaddress = $mail->fromAddress;
					$source = "2";
					$phone = "";
					$priority = '1';
					
					if ($this->TicketController->create_user($fromaddress, $fromname, $subject, $body, $phone, $helptopic, $sla, $priority, $source, $collaborator, $e_mail->department) == true) {
							$thread_id = Ticket_Thread::whereRaw('id = (select max(`id`) from ticket_thread)')->first();
							$thread_id = $thread_id->id;
					
						foreach($mail->getAttachments() as $attachment)
						{
							$filepath = explode('../../../../../public/',$attachment->filePath);
                            $path = $_SERVER["DOCUMENT_ROOT"]."/support/code/public/".$filepath[1];
							$filesize = filesize($path);
							$file_data = file_get_contents($path);
							$ext = pathinfo($attachment->filePath, PATHINFO_EXTENSION);
							$imageid = $attachment->id;
							$string = str_replace('-', '', $attachment->name);
							$filename = explode('src', $attachment->filePath);
							$filename = str_replace('\\', '', $filename);
							$body = str_replace("cid:".$imageid, $filepath[1], $body);	
							$pos = strpos($body, $filepath[1]);


							if($pos == false) {

                                if($settings_email->first()->attachment == 1) {
		     				        $upload = new Ticket_attachments;
                					$upload->file = $file_data;
               						$upload->thread_id = $thread_id;
		        					$upload->name = $filepath[1];
			        				$upload->type = $ext;
				         			$upload->size = $filesize;
					         		$upload->poster = "ATTACHMENT";
						         	$upload->save();
                                }
							} else {
							$upload = new Ticket_attachments;
							$upload->file = $file_data;
							$upload->thread_id = $thread_id;
							$upload->name = $filepath[1];
							$upload->type = $ext;
							$upload->size = $filesize;
							$upload->poster = "INLINE";
							$upload->save();
							}
							unlink($path);
						}
						$body = Encoding::fixUTF8($body);
						$thread = Ticket_Thread::where('id','=',$thread_id)->first();
						$thread->body = $body;
						$thread->save();
					}
				}
			}
		}
		}
		}
	}

	/**
	 * Decode Imap text
	 * @param type $str
	 * @return type string
	 */
	public function decode_imap_text($str) {
		$result = '';
		$decode_header = imap_mime_header_decode($str);
		foreach ($decode_header AS $obj) {
			$result .= htmlspecialchars(rtrim($obj->text, "\t"));
		}
		return $result;
	}

	/**
	 * fetch_attachments
	 * @return type
	 */
	public function fetch_attachments(){
		$uploads = Upload::all();
		foreach($uploads as $attachment) {
			$image = @imagecreatefromstring($attachment->file); 
	        ob_start();
	        imagejpeg($image, null, 80);
	        $data = ob_get_contents();
	        ob_end_clean();
	        $var = '<a href="" target="_blank"><img src="data:image/jpg;base64,' . base64_encode($data)  . '"/></a>';
	        echo '<br/><span class="mailbox-attachment-icon has-img">'.$var.'</span>';
	    }
	}

	/**
	 * function to load data
	 * @param type $id 
	 * @return type file
	 */
	public function get_data($id){
		$attachments = App\Model\Ticket\Ticket_attachments::where('id','=',$id)->get();
		foreach($attachments as $attachment)
		{
    			header('Content-type: application/'.$attachment->type.'');
				header('Content-Disposition: inline; filename='.$attachment->name.'');
				header('Content-Transfer-Encoding: binary');
				echo $attachment->file;
    	}
	}

}
