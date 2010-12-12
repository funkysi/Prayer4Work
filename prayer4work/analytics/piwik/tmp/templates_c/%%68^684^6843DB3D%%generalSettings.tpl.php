<?php /* Smarty version 2.6.26, created on 2010-11-21 15:48:56
         compiled from /var/www/wordpress2/analytics/piwik/plugins/CoreAdminHome/templates/generalSettings.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'loadJavascriptTranslations', '/var/www/wordpress2/analytics/piwik/plugins/CoreAdminHome/templates/generalSettings.tpl', 4, false),array('function', 'ajaxErrorDiv', '/var/www/wordpress2/analytics/piwik/plugins/CoreAdminHome/templates/generalSettings.tpl', 8, false),array('function', 'ajaxLoadingDiv', '/var/www/wordpress2/analytics/piwik/plugins/CoreAdminHome/templates/generalSettings.tpl', 9, false),array('modifier', 'translate', '/var/www/wordpress2/analytics/piwik/plugins/CoreAdminHome/templates/generalSettings.tpl', 6, false),array('modifier', 'inlineHelp', '/var/www/wordpress2/analytics/piwik/plugins/CoreAdminHome/templates/generalSettings.tpl', 30, false),)), $this); ?>
<?php $this->assign('showSitesSelection', false); ?>
<?php $this->assign('showPeriodSelection', false); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php echo smarty_function_loadJavascriptTranslations(array('plugins' => 'UsersManager'), $this);?>


<h2><?php echo ((is_array($_tmp='General_GeneralSettings')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>

<?php echo smarty_function_ajaxErrorDiv(array('id' => 'ajaxError'), $this);?>

<?php echo smarty_function_ajaxLoadingDiv(array('id' => 'ajaxLoading'), $this);?>

<table class="adminTable adminTableNoBorder" style='width:900px;'>
<tr>
	<td style='width:400px'><?php echo ((is_array($_tmp='General_AllowPiwikArchivingToTriggerBrowser')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
	<td style='width:220px'>
	<fieldset>
		<label><input type="radio" value="1" name="enableBrowserTriggerArchiving"<?php if ($this->_tpl_vars['enableBrowserTriggerArchiving'] == 1): ?> checked="checked"<?php endif; ?> /> 
			<?php echo ((is_array($_tmp='General_Yes')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <br />
			<span class="form-description"><?php echo ((is_array($_tmp='General_Default')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
		</label><br /><br />
		
		<label><input type="radio" value="0" name="enableBrowserTriggerArchiving"<?php if ($this->_tpl_vars['enableBrowserTriggerArchiving'] == 0): ?> checked="checked"<?php endif; ?> /> 
			<?php echo ((is_array($_tmp='General_No')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <br />
			<span class="form-description"><?php echo ((is_array($_tmp='General_ArchivingTriggerDescription')) ? $this->_run_mod_handler('translate', true, $_tmp, "<a href='misc/redirectToUrl.php?url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>", "</a>") : smarty_modifier_translate($_tmp, "<a href='misc/redirectToUrl.php?url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>", "</a>")); ?>
</span>
		</label> 
	</fieldset>
	<td>
	<?php ob_start(); ?>
		<?php echo ((is_array($_tmp='General_ArchivingInlineHelp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
<br /> 
		<?php echo ((is_array($_tmp='General_SeeTheOfficialDocumentationForMoreInformation')) ? $this->_run_mod_handler('translate', true, $_tmp, "<a href='misc/redirectToUrl.php?url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>", "</a>") : smarty_modifier_translate($_tmp, "<a href='misc/redirectToUrl.php?url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>", "</a>")); ?>

	<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('browserArchivingHelp', ob_get_contents());ob_end_clean(); ?>
	<?php echo ((is_array($_tmp=$this->_tpl_vars['browserArchivingHelp'])) ? $this->_run_mod_handler('inlineHelp', true, $_tmp) : smarty_modifier_inlineHelp($_tmp)); ?>
	</td>
	</td>
</tr>
<tr>
	<td><label for="todayArchiveTimeToLive"><?php echo ((is_array($_tmp='General_ReportsForTodayWillBeProcessedAtMostEvery')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label></td>
	<td>
		<?php echo ((is_array($_tmp='General_NSeconds')) ? $this->_run_mod_handler('translate', true, $_tmp, "<input size='3' value='".($this->_tpl_vars['todayArchiveTimeToLive'])."' id='todayArchiveTimeToLive' />") : smarty_modifier_translate($_tmp, "<input size='3' value='".($this->_tpl_vars['todayArchiveTimeToLive'])."' id='todayArchiveTimeToLive' />")); ?>
 
	</td>
	<td width='450px'>
	<?php ob_start(); ?>
		<?php if ($this->_tpl_vars['showWarningCron']): ?>
			<strong>
			<?php echo ((is_array($_tmp='General_NewReportsWillBeProcessedByCron')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
<br/>
			<?php echo ((is_array($_tmp='General_ReportsWillBeProcessedAtMostEveryHour')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

			<?php echo ((is_array($_tmp='General_IfArchivingIsFastYouCanSetupCronRunMoreOften')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
<br/>
			</strong>
		<?php endif; ?>
		<?php echo ((is_array($_tmp='General_SmallTrafficYouCanLeaveDefault')) ? $this->_run_mod_handler('translate', true, $_tmp, 10) : smarty_modifier_translate($_tmp, 10)); ?>
<br /> 
		<?php echo ((is_array($_tmp='General_MediumToHighTrafficItIsRecommendedTo')) ? $this->_run_mod_handler('translate', true, $_tmp, 1800, 3600) : smarty_modifier_translate($_tmp, 1800, 3600)); ?>

	<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('archiveTodayTTLHelp', ob_get_contents());ob_end_clean(); ?>
	<?php echo ((is_array($_tmp=$this->_tpl_vars['archiveTodayTTLHelp'])) ? $this->_run_mod_handler('inlineHelp', true, $_tmp) : smarty_modifier_inlineHelp($_tmp)); ?>
	</td>
	</td>
</tr>

<div id='emailSettings'>
<table class="adminTable adminTableNoBorder" style='width:600px;'>
	<tr>
		<td><?php echo ((is_array($_tmp='General_UseSMTPServerForEmail')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label><br>
			<span class="form-description"><?php echo ((is_array($_tmp='General_SelectYesIfYouWantToSendEmailsViaServer')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
		</td>
		<td style='width:200px'>
			<label><input type="radio" name="mailUseSmtp" value="1" <?php if ($this->_tpl_vars['mail']['transport'] == 'smtp'): ?> checked <?php endif; ?>> <?php echo ((is_array($_tmp='General_Yes')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label>
			<label><input type="radio" name="mailUseSmtp" value="0" style ="margin-left:20px;" <?php if ($this->_tpl_vars['mail']['transport'] == ''): ?> checked <?php endif; ?>>  <?php echo ((is_array($_tmp='General_No')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label> 
		</td>
	</tr>
</table>


<div id='smtpSettings'>
	<table class="adminTable adminTableNoBorder" style='width:550px;'>	
		<tr>
			<td><label for="mailHost"><?php echo ((is_array($_tmp='General_SmtpServerAddress')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label></td>
			<td style='width:200px'><input type="text" id="mailHost" value="<?php echo $this->_tpl_vars['mail']['host']; ?>
"></td>
		</tr>
		<tr>
			<td><label for="mailPort"><?php echo ((is_array($_tmp='General_SmtpPort')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label></td>
			<td><input type="text" id="mailPort" value="<?php echo $this->_tpl_vars['mail']['port']; ?>
"></td>
		</tr>
		<tr>
			<td><label for="mailType"><?php echo ((is_array($_tmp='General_AuthenticationMethodSmtp')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label><br>
				<span class="form-description"><?php echo ((is_array($_tmp='General_OnlyUsedIfUserPwdIsSet')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span>
			</td>
			<td>
				<select id="mailType" >
					<option value="" <?php if ($this->_tpl_vars['mail']['type'] == ''): ?> selected="selected" <?php endif; ?>></option>
					<option id="plain" <?php if ($this->_tpl_vars['mail']['type'] == 'PLAIN'): ?> selected="selected" <?php endif; ?> value="PLAIN">PLAIN</option>
					<option id="login" <?php if ($this->_tpl_vars['mail']['type'] == 'LOGIN'): ?> selected="selected" <?php endif; ?> value="LOGIN"> LOGIN</option>
					<option id="cram-md5" <?php if ($this->_tpl_vars['mail']['type'] == 'CRAM-MD5'): ?> selected="selected" <?php endif; ?> value="CRAM-MD5"> CRAM-MD5</option>
					<option id="digest-md5" <?php if ($this->_tpl_vars['mail']['type'] == 'DIGEST-MD5'): ?> selected="selected" <?php endif; ?> value="DIGEST-MD5"> DIGEST-MD5</option>
					<option id="pop-before-smtp" <?php if ($this->_tpl_vars['mail']['type'] == 'POP-before-SMTP'): ?> selected="selected" <?php endif; ?> value="POP-before-SMTP"> POP-before-SMTP</option>
				</select> 
			</td>
		</tr>
		<tr>
			<td><label for="mailUsername"><?php echo ((is_array($_tmp='General_SmtpUsername')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label><br>
				<span class="form-description"><?php echo ((is_array($_tmp='General_OnlyEnterIfRequired')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</i></td>
			<td>
				<input type="text" id="mailUsername" value = "<?php echo $this->_tpl_vars['mail']['username']; ?>
" >
			</td>
		</tr>
		<tr>
			<td><label for="mailPassword"><?php echo ((is_array($_tmp='General_SmtpPassword')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label><br>
				<span class="form-description"><?php echo ((is_array($_tmp='General_OnlyEnterIfRequiredPassword')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
<br/>
				<?php echo ((is_array($_tmp='General_WarningPasswordStored')) ? $this->_run_mod_handler('translate', true, $_tmp, "<strong>", "</strong>") : smarty_modifier_translate($_tmp, "<strong>", "</strong>")); ?>
</span>
			</td>
			<td>
				<input type="password" id="mailPassword" value = "<?php echo $this->_tpl_vars['mail']['password']; ?>
" >
			</td>
		</tr>
	</table>
</div>

</table>
<input type="submit" value="<?php echo ((is_array($_tmp='General_Save')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" id="generalSettingsSubmit" class="submit" />
<br /><br />

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>