<?php function sidebar_row($page, $icon, $label){ ?>
			<li class="sidebar-item" <?php if(MENU_SEL == $page){ ?> selected <?php } ?> >
			<a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=$page?>" aria-expanded="false">
				<i class="mdi mdi-<?=$icon?>"></i><span	class="hide-menu"><?=$label?></span>
			</a>
		</li>
<?php } ?>

<aside class="left-sidebar toggled" data-sidebarbg="skin6">
	<div class="scroll-sidebar toggled" style="padding-left:10px">
		<nav class="sidebar-nav toggled"><br>
			<ul id="sidebarnav">
				<?php
				sidebar_row('index.php', 					'view-dashboard',						'Dashboard');
				
				if($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') {	// only admins have acess to user/group creation
					if($_SESSION[SESS_USR_KEY]->id == SUPER_ADMIN_ID) {
						sidebar_row('settings.php', 			'language-css3', 'Settings');
					}
					sidebar_row('users.php', 					'account-settings-variant', 'Users');
					sidebar_row('access_groups.php',	'account-multiple', 				'User Groups');
					sidebar_row('links.php',					'database',							 				'Data Sources');
					sidebar_row('maps.php',						'map',							 				'Maps');
					sidebar_row('permalinks.php',			'share-variant',							 				'Share');
				}
				sidebar_row('../index.php',				'exit-to-app',							'Front End');
				sidebar_row('../logout.php',			'logout',										'Log Out');
				?>
			</ul>
		</nav>
	</div>
</aside>
