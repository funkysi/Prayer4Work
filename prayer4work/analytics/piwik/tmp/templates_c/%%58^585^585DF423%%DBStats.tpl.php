<?php /* Smarty version 2.6.26, created on 2011-01-04 23:18:06
         compiled from /var/www/prayer4work/analytics/piwik/plugins/DBStats/templates/DBStats.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', '/var/www/prayer4work/analytics/piwik/plugins/DBStats/templates/DBStats.tpl', 6, false),)), $this); ?>
<?php $this->assign('showSitesSelection', false); ?>
<?php $this->assign('showPeriodSelection', false); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<div style="max-width:980px;">

<h2><?php echo ((is_array($_tmp='DBStats_DatabaseUsage')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
<?php $this->assign('totalSize', $this->_tpl_vars['tablesStatus']['Total']['Total_length']); ?>
<p><?php echo ((is_array($_tmp='DBStats_MainDescription')) ? $this->_run_mod_handler('translate', true, $_tmp, $this->_tpl_vars['totalSize']) : smarty_modifier_translate($_tmp, $this->_tpl_vars['totalSize'])); ?>

<br />
<?php echo ((is_array($_tmp='DBStats_LearnMore')) ? $this->_run_mod_handler('translate', true, $_tmp, "<a href='?module=Proxy&action=redirect&url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>Piwik Auto Archiving</a>") : smarty_modifier_translate($_tmp, "<a href='?module=Proxy&action=redirect&url=http://piwik.org/docs/setup-auto-archiving/' target='_blank'>Piwik Auto Archiving</a>")); ?>
</p>
<table class="adminTable">
	<thead>
		<th><?php echo ((is_array($_tmp='DBStats_Table')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th><?php echo ((is_array($_tmp='DBStats_RowCount')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th><?php echo ((is_array($_tmp='DBStats_DataSize')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th><?php echo ((is_array($_tmp='DBStats_IndexSize')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th><?php echo ((is_array($_tmp='DBStats_TotalSize')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
	</thead>
	<tbody id="tables">
		<?php $_from = $this->_tpl_vars['tablesStatus']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['index'] => $this->_tpl_vars['table']):
?>
		<tr <?php if ($this->_tpl_vars['table']['Name'] == 'Total'): ?>class="active" style="font-weight:bold;"<?php endif; ?>>
			<td>
				<?php echo $this->_tpl_vars['table']['Name']; ?>

			</td> 
			<td>
				<?php echo $this->_tpl_vars['table']['Rows']; ?>

			</td> 
			<td>
				<?php echo $this->_tpl_vars['table']['Data_length']; ?>
b
			</td> 
			<td>
				<?php echo $this->_tpl_vars['table']['Index_length']; ?>
b
			</td> 
			<td>
				<?php echo $this->_tpl_vars['table']['Total_length']; ?>
b
			</td> 
		</tr>
		<?php endforeach; endif; unset($_from); ?>
	</tbody>
</table>

</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CoreAdminHome/templates/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>