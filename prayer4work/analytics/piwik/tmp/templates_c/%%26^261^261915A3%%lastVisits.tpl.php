<?php /* Smarty version 2.6.26, created on 2010-11-21 10:59:51
         compiled from /var/www/wordpress2/analytics/piwik/plugins/Live/templates/lastVisits.tpl */ ?>
<?php $_from = $this->_tpl_vars['visitors']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['visitor']):
?>
	<div id="<?php echo $this->_tpl_vars['visitor']['idVisit']; ?>
" class="visit<?php if ($this->_tpl_vars['visitor']['idVisit'] % 2): ?> alt<?php endif; ?>">
		<div style="display:none" class="idvisit"><?php echo $this->_tpl_vars['visitor']['idVisit']; ?>
</div>
			<div class="datetime">
				<?php echo $this->_tpl_vars['visitor']['serverDatePretty']; ?>
 - <?php echo $this->_tpl_vars['visitor']['serverTimePretty']; ?>

				&nbsp;<img src="<?php echo $this->_tpl_vars['visitor']['countryFlag']; ?>
" title="<?php echo $this->_tpl_vars['visitor']['country']; ?>
, Provider <?php echo $this->_tpl_vars['visitor']['provider']; ?>
" />
				&nbsp;<img src="<?php echo $this->_tpl_vars['visitor']['browserIcon']; ?>
" title="<?php echo $this->_tpl_vars['visitor']['browser']; ?>
 with plugins <?php echo $this->_tpl_vars['visitor']['plugins']; ?>
 enabled" />
				&nbsp;<img src="<?php echo $this->_tpl_vars['visitor']['operatingSystemIcon']; ?>
" title="<?php echo $this->_tpl_vars['visitor']['operatingSystem']; ?>
, <?php echo $this->_tpl_vars['visitor']['resolution']; ?>
" />
				&nbsp;<?php if ($this->_tpl_vars['visitor']['isVisitorGoalConverted']): ?><img src="<?php echo $this->_tpl_vars['visitor']['goalIcon']; ?>
" title="<?php echo $this->_tpl_vars['visitor']['goalType']; ?>
" /><?php endif; ?>
				<?php if ($this->_tpl_vars['visitor']['isVisitorReturning']): ?>&nbsp;<img src="plugins/Live/templates/images/returningVisitor.gif" title="Returning Visitor" /><?php endif; ?>
				&nbsp;<label id="" title="IP: <?php echo $this->_tpl_vars['visitor']['ip']; ?>
 - Duration: <?php echo $this->_tpl_vars['visitor']['visitLengthPretty']; ?>
">more...</label>
			</div>
			<!--<div class="settings"></div>-->
			<div class="referer">
				<?php if ($this->_tpl_vars['visitor']['refererType'] != 'directEntry'): ?>from <a href="<?php echo $this->_tpl_vars['visitor']['refererUrl']; ?>
" target="_blank"><?php if (! empty ( $this->_tpl_vars['visitor']['searchEngineIcon'] )): ?><img src="<?php echo $this->_tpl_vars['visitor']['searchEngineIcon']; ?>
" /> <?php endif; ?><?php echo $this->_tpl_vars['visitor']['refererName']; ?>
</a>
					<?php if (! empty ( $this->_tpl_vars['visitor']['keywords'] )): ?>"<?php echo $this->_tpl_vars['visitor']['keywords']; ?>
"<?php endif; ?>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['visitor']['refererType'] == 'directEntry'): ?>Direct entry<?php endif; ?>
			</div>
		<div id="<?php echo $this->_tpl_vars['visitor']['idVisit']; ?>
_actions" class="settings">
			<span class="pagesTitle">Pages:</span>&nbsp;
			<?php  $col = 0;	 ?>
			<?php $_from = $this->_tpl_vars['visitor']['actionDetails']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['action']):
?>
			  <?php 
			  	$col++;
		  		if ($col>=9)
		  		{
				  $col=0;
		  		}
				 ?>
				<a href="<?php echo $this->_tpl_vars['action']['pageUrl']; ?>
" target="_blank"><img align="middle" src="plugins/Live/templates/images/file<?php  echo $col;  ?>.png" title="<?php echo $this->_tpl_vars['action']['pageUrl']; ?>
" /></a>
			<?php endforeach; endif; unset($_from); ?>
		</div>
	</div>
<?php endforeach; endif; unset($_from); ?>