<?php if ($this->_var['user_info']): ?>
	<span class="li li_no"><a href="<?php
echo parse_url_tag("u:shop|uc_center|"."".""); 
?>"><?php echo $this->_var['user_info']['user_name']; ?></a></span>
	<span class="li"><span class="pm <?php if ($this->_var['msg_count'] > 0 || $this->_var['user_info']['videopassed'] != 1 || $this->_var['user_info']['idcardpassed'] != 1 || $this->_var['user_info']['creditpassed'] != 1 || $this->_var['user_info']['workpassed'] != 1 || $this->_var['user_info']['incomepassed'] != 1): ?>new_pm <?php endif; ?>"></span><a href="<?php
echo parse_url_tag("u:shop|uc_msg|"."".""); 
?>" class="msg_count"><?php echo $this->_var['LANG']['MSG_COUNT']; ?><?php if ($this->_var['msg_count'] > 0): ?>(<?php echo $this->_var['msg_count']; ?>)<?php endif; ?></a></span>
	<span class="li"><a href="<?php
echo parse_url_tag("u:shop|user#loginout|"."".""); 
?>"><?php echo $this->_var['LANG']['LOGINOUT']; ?></a></span>
	<?php if ($this->_var['user_info']['videopassed'] != 1 || $this->_var['user_info']['idcardpassed'] != 1 || $this->_var['user_info']['creditpassed'] != 1 || $this->_var['user_info']['workpassed'] != 1 || $this->_var['user_info']['incomepassed'] != 1): ?>
	<div class="tip_box f_l ps clearfix" style="display:none;">
		<div class="cl ps"><a href="#" class="close">×</a></div>

		<div class="blank1"></div>
	</div>
	<?php endif; ?>
	<?php else: ?>
	<span class="li li_no"><?php echo $this->_var['LANG']['TOURIST']; ?></span>
	<span class="li"><a href="<?php
echo parse_url_tag("u:shop|user#register|"."".""); 
?>"><?php echo $this->_var['LANG']['FREE_REGISTER']; ?></a></span>
	<span class="li"><a href="<?php
echo parse_url_tag("u:shop|user#login|"."".""); 
?>"><?php echo $this->_var['LANG']['LOGIN']; ?></a></span>
<?php endif; ?>
