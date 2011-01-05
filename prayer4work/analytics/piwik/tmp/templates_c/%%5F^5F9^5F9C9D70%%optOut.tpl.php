<?php /* Smarty version 2.6.26, created on 2011-01-04 23:16:57
         compiled from /var/www/prayer4work/analytics/piwik/plugins/CoreAdminHome/templates/optOut.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', '/var/www/prayer4work/analytics/piwik/plugins/CoreAdminHome/templates/optOut.tpl', 5, false),)), $this); ?>
<html>
	<head>
	</head>
	<body>
		<?php if (! $this->_tpl_vars['trackVisits']): ?><?php echo ((is_array($_tmp='CoreAdminHome_OptOutComplete')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		<br/>
		<?php echo ((is_array($_tmp='CoreAdminHome_OptOutCompleteBis')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		<?php else: ?>
		<?php echo ((is_array($_tmp='CoreAdminHome_YouMayOptOut')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 
		<br/>
		<?php echo ((is_array($_tmp='CoreAdminHome_YouMayOptOutBis')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 
		<?php endif; ?>
		<br/><br/> 
		<form method="post" action="?module=CoreAdminHome&amp;action=optOut">
			<input type="hidden" name="nonce" value="<?php echo $this->_tpl_vars['nonce']; ?>
"></input>
			<input onclick="this.form.submit()" type="checkbox" id="trackVisits" name="trackVisits" <?php if ($this->_tpl_vars['trackVisits']): ?>checked="checked"<?php endif; ?>><label for="trackVisits"><strong><?php if ($this->_tpl_vars['trackVisits']): ?><?php echo ((is_array($_tmp='CoreAdminHome_YouAreOptedIn')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp='CoreAdminHome_YouAreOptedOut')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <?php endif; ?></strong></a></input>
		</form>
	</body>
</html>