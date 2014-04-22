<?php
	define('PATH_MOD_OOLEMONSTANDBOX', realpath(dirname(__FILE__) . '/../'));

	class ooLemonStandBox_Module extends Core_ModuleBase
	{

		static public $configured = false;

		/**
		 * Creates the module information object
		 * @return Core_ModuleInfo
		 */
		protected function create_module_info()
		{
			return new Core_ModuleInfo(
				"ooLemonStandBox",
				"LemonStand Protobox companion module to help with development",
				"OpenOrchard" );
		}
		
		public function subscribe_events()
		{
			Backend::$events->addEvent('cms:onGetTemplateContent', $this, 'frontend_page_request');
			Backend::$events->addEvent('cms:onBeforeResourceCombine', $this, 'combine_resources');
			Backend::$events->addEvent('onLogin', $this, 'on_admin_login');
			Backend::$events->addEvent('backend:onControllerReady', $this, 'backend_controller_ready');
			// core:onBeforeSoftwareUpdate
			// core:onAfterSoftwareUpdate
		}	

		public static function configure()
		{

			$sapi = php_sapi_name();
			
			if ($sapi == 'cli')
				return;
			
			if (array_key_exists('SHELL', $_SERVER) && strlen($_SERVER['SHELL']))
				return;
				
			if (!array_key_exists('DOCUMENT_ROOT', $_SERVER) || !strlen($_SERVER['DOCUMENT_ROOT']))
				return;

			$config = Core_ModuleSettings::create('oolemonstandbox','lemonstandbox-settings');

			if(!$config->is_new_record())
			{
				self::$configured = true;
			}
		}

		public function frontend_page_request($data)
		{
			if(self::$configured) return $data;

			$config = Core_ModuleSettings::create('oolemonstandbox','lemonstandbox-settings');

			$config->module_path = post_array_item('Core_ModuleSettings', 'module_path',$config->module_path);
			$config->theme_path = post_array_item('Core_ModuleSettings', 'theme_path',$config->theme_path);
			$config->template_path = post_array_item('Core_ModuleSettings','template_path',$config->template_path);

			if(Phpr::$request->isPostBack())
			{
				$config->save();
				//Set configured flag
				self::$configured = true;
				//Update Config
				ooLemonStandBox_ConfigManager::updateConfig();
				//Update Modules
				ooLemonStandBox_UpdateManager::update();
				//Update CMS
				ooLemonStandBox_ConfigManager::updateCMS();
				//redirect to home
				Phpr::$response->redirect('/');
			}else{


				$controller = new Core_Settings();
				$controller->edit('oolemonstandbox','lemonstandbox-settings');
				
				$data['content'] = $controller->loadView('edit');
			}

			return $data;

		}	

		public function listSettingsForms()
		{
			return array(
					'lemonstandbox-settings'=>array(
					'icon'=> PATH_MOD_OOLEMONSTANDBOX . '/resources/images/settings.png',
					'title'=>'LemonStandBox Settings',
					'description'=>'Configure locations and settings regarding lemonstandbox',
					'sort_id'=>100,
					'section'=>'System'
					)
				);
		}

		public function buildSettingsForm($model, $form_code)
		{
			$model->add_field('module_path', 'Development Module Path', 'full', db_varchar);
			$model->add_field('theme_path', 'Development Theme Path', 'full', db_varchar);
			$model->add_field('template_path', 'Development Template Path', 'full', db_varchar);
		}

		public function initSettingsData($model, $form_code)
		{

			$model->module_path = PATH_PROTOBOX . '/modules/';
			$model->theme_path = PATH_PROTOBOX . '/themes/';
			$model->template_path = PATH_PROTOBOX . '/themes/';
			
			// /*
			//  * Grouped products are disabled in favor of Option Matrix
			//  * See http://v1.lemonstand.com/docs/understanding_option_matrix/
			//  */

			// $CONFIG['CACHE_SHIPPING_METHODS']		= false;
			// $CONFIG['ENABLE_BACKEND_TOUR']			= false;

			// // TEMPLATE_PATH
			// $CONFIG['TEMPLATE_PATH']				= '/srv/www/web/themes';
			// $CONFIG['ALLOWED_RESOURCE_PATHS']		= array( PATH_APP, '/srv/www/web/themes' );
			// $CONFIG['RESOURCE_SYMLINKS']			= array( '//themes'=>'/srv/www/web/themes' );

			// $CONFIG['DISABLE_USAGE_STATISTICS']		= false;
			// $CONFIG['AUTO_CHECK_UPDATES']			= false;

			// $CONFIG['CMS_FILEBASED_TEMPLATES']		= true;
			// $CONFIG['CMS_CONTENT_FILE_EXT']			= 'php';
			// $CONFIG['CMS_RESOURCES_DIR']			= 'resources';

		}

		public function validateSettingsData($model, $form_code)
		{

		}

		public function getSettingsFieldOptions($model, $form_code, $field_code)
		{

		}

		/**
		 * Backend Controller ready handler
		 *
		 * Replaces the viewPath of the module controllers
		 * with correct location for development paths.
		 * 
		 * @param  Backend_Controller 	$controller an instance of a backend controller
		 * @return void             
		 */
		public function backend_controller_ready($controller)
		{
			$module_id = $controller->getModuleId();

			if(in_array($module_id, explode('|', 'blog|backend|users|system|shop|session|oolemonstandbox|core|cms'))) return;

			$config = Core_ModuleSettings::create('oolemonstandbox','lemonstandbox-settings');

			$controller->viewPath = $config->module_path . $module_id .'/controllers/'.strtolower(get_class($controller));

		}

		/**
		 * Admin Login event handler
		 */
		public function on_admin_login()
		{
			ooLemonStandBox_UpdateManager::update();
		}
		/**
		 * Excerpt from original combine resources. 
		 * @param  array $args  argument array
		 * @return string       url or script tag
		 * @see CMS_Controller
		 */
		public function combine_resources($args)
		{
			extract($args);

			$files = Phpr_Util::splat($files);
			
			$current_theme = null;
			if (Cms_Theme::is_theming_enabled() && ($theme = Cms_Theme::get_active_theme()))
				$current_theme = $theme;
			
			$files_array = array();
			foreach ($files as $file)
			{
				$file = trim($file);
				
				if (substr($file, 0, 1) == '@')
				{
					$file = substr($file, 1);
					if (strpos($file, '/') !== 0)
						$file = '/'.$file;

					if ($current_theme)
						$file = '/..' . $theme->get_resources_path().$file;
					else 
						$file = '/'.Cms_SettingsManager::get()->resources_dir_path.$file;
				}
					
				$files_array[] = 'file%5B%5D='. urlencode(trim($file));
			}
				
			$options_str = array();
			foreach ($options as $option=>$value)
			{
				if ($value)
					$options_str[] = $option.'=1';
			}
			
			$options_str = implode('&amp;', $options_str);
			if ($options_str)
				$options_str = '&amp;'.$options_str;
			
			if ($type == 'javascript') {
				$url = root_url('ls_javascript_combine/?'.implode('&amp;', $files_array).$options_str);
				
				return $show_tag ? '<script type="text/javascript" src="'.$url.'"></script>'."\n" : $url;
			}
			else {
				$url = root_url('ls_css_combine/?'.implode('&amp;', $files_array).$options_str);
				
				return $show_tag ? '<link rel="stylesheet" type="text/css" href="'.$url.'" />' : $url;
			}

		}
		


		/*
		 * Awaiting Deprecation
		 */
		// public function listSettingsItems()
		// {
		// 	return $this->list_settings_items();
		// }

		protected function createModuleInfo()
		{
			return $this->create_module_info();
		}
		
		public function subscribeEvents()
		{
			return $this->subscribe_events();
		}


	}
