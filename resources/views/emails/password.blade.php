Hello <br/><br/>

You asked to reset your Sync password. To do so, please click this link:<br/><br/>

	{!! url('password/reset/'.$token) !!}    <br/><br/>

<br/>
This will let you change your password to something new. If you didn't ask for this, don't worry, we'll keep your password safe.<br/><br/>

Thank You.<br/>
<br/>
Kind regards,<br/><br/>
<?php $company =  App\Model\Settings\Company::where('id','=','1')->first(); $company = $company->company_name;  ?>
{!! $company !!}