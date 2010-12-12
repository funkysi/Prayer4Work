<?php /* Smarty version 2.6.26, created on 2010-12-03 23:20:05
         compiled from /var/www/prayer4work/analytics/piwik/plugins/SecurityInfo/templates/index.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'loadJavascriptTranslations', '/var/www/prayer4work/analytics/piwik/plugins/SecurityInfo/templates/index.tpl', 4, false),array('modifier', 'translate', '/var/www/prayer4work/analytics/piwik/plugins/SecurityInfo/templates/index.tpl', 6, false),)), $this); ?>
<?php $this->assign('showSitesSelection', false); ?>
<?php $this->assign('showPeriodSelection', false); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php echo smarty_function_loadJavascriptTranslations(array('plugins' => 'SecurityInfo'), $this);?>


<h2><?php echo ((is_array($_tmp='SecurityInfo_SecurityInformation')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
<p><?php echo ((is_array($_tmp='SecurityInfo_PluginDescription')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</p>

<div style="max-width:980px;">
<?php $_from = $this->_tpl_vars['results']['test_results']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['section']):
?>
<h2><?php echo $this->_tpl_vars['i']; ?>
</h2>
<table class="adminTable">
	<thead>
		<tr>
		<th><?php echo ((is_array($_tmp='SecurityInfo_Test')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th><?php echo ((is_array($_tmp='SecurityInfo_Result')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		</tr>
	</thead>
	<tbody>
		<?php $_from = $this->_tpl_vars['section']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['j'] => $this->_tpl_vars['test']):
?>
		<tr>
			<td><?php echo $this->_tpl_vars['j']; ?>
</td>
			<td style="<?php if ($this->_tpl_vars['test']['result'] == -1): ?>background-color:green;color:white;<?php elseif ($this->_tpl_vars['test']['result'] == -2): ?>background-color:yellow;color:black;<?php else: ?>background-color:red;color:white;<?php endif; ?>"><?php echo $this->_tpl_vars['test']['message']; ?>
</td>
		</tr>
		<?php endforeach; endif; unset($_from); ?>
	</tbody>
</table>
<?php endforeach; endif; unset($_from); ?>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>