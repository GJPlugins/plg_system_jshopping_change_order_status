<?php
    /**
     * @package    plg_system_jshopping_change_order_status
     *
     * @author     oleg <your@email.com>
     * @copyright  A copyright
     * @license    GNU General Public License version 2 or later; see LICENSE.txt
     * @link       http://your.url.com
     */

    defined('_JEXEC') or die;

    use Joomla\CMS\Application\CMSApplication;
    use Joomla\CMS\Plugin\CMSPlugin;
    use \Joomla\CMS\Date\Date;
    use Joomla\CMS\Uri\Uri;


    /**
     * Plg_system_jshopping_change_order_status plugin.
     *
     * @package   plg_system_jshopping_change_order_status
     * @since     1.0.0
     */
    class plgSystemPlg_system_jshopping_change_order_status extends CMSPlugin
    {
        /**
         * Application object
         *
         * @var    CMSApplication
         * @since  1.0.0
         */
        protected $app;

        /**
         * Database object
         *
         * @var    JDatabaseDriver
         * @since  1.0.0
         */
        protected $db;

        /**
         * Affects constructor behavior. If true, language files will be loaded automatically.
         *
         * @var    boolean
         * @since  1.0.0
         */
        protected $autoloadLanguage = true;

        /**
         * onAfterInitialise.
         *
         * @return  void
         *
         * @since   1.0
         */
        public function onAfterInitialise()
        {

        }

        /**
         * onAfterRoute.
         *
         * @return  void
         *
         * @since   1.0
         */
        public function onAfterRoute()
        {

        }

        /**
         * onAfterDispatch.
         *
         * @return  void
         *
         * @since   1.0
         */
        public function onAfterDispatch()
        {

        }

        /**
         *
         *
         * @since version
         */
        public function onBeforeRender()
        {

            $app = \Joomla\CMS\Factory::getApplication();
            if( $app->isClient('administrator') ) return; #END IF

            $doc = \Joomla\CMS\Factory::getDocument();

            # Дата последнего запуска
            $last_update_time = $this->params->get('last_update_time' , '2021-09-16 00:00:00');

            $date = new Date();
            $last_date = new Date( $last_update_time );

            /**
             * @var float интервал запуска в секундах
             *
             * 60 - 1 час
             * 1440 - сутки
             *
             */
            $interval_upd = $this->params->get('interval_upd' , 60/* 1 час */) * 60  ;

//            echo'<pre>';print_r( $interval_upd );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;
//            echo'<pre>';print_r( $date->getTimestamp() - $last_date->getTimestamp() );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;

            $doc->addScriptOptions( $this->_name , ['debug_on' => 0 ]);
            if( $this->params->get('debug_on' , false ) )
            {

                $delta = $interval_upd - ( $date->getTimestamp()-$last_date->getTimestamp()) ;
                $deltaTxt = $delta <= 0 ? "'Запуск plg_system_jshopping_change_order_status СЕЙЧАС'" : "'До запуска plg_system_jshopping_change_order_status " . $delta ." сек.'" ;
                $doc->addScriptDeclaration(";console.log(". $deltaTxt .");");
                $doc->addScriptOptions( $this->_name , ['debug_on' => 1 ]);
            }#END IF


            # Если интервал (сек.) < меньше чем (текущее время - Время последнего запуска)
            if( $interval_upd > ( $date->getTimestamp() - $last_date->getTimestamp()) ) return; #END IF


            try
            {
                JLoader::registerNamespace('GNZ11' , JPATH_LIBRARIES . '/GNZ11' , $reset = false , $prepend = false , $type = 'psr4');
                $GNZ11_js = \GNZ11\Core\Js::instance();
            } catch ( Exception $e )
            {
                if( !\Joomla\CMS\Filesystem\Folder::exists($this->patchGnz11) && $this->app->isClient('administrator') )
                {
                    $this->app->enqueueMessage('Должна быть установлена библиотека GNZ11' , 'error');
                }#END IF
            }
            \GNZ11\Core\Js::addJproLoad(
                Uri::root() . 'plugins/system/plg_system_jshopping_change_order_status/assets/js/plg_system_jshopping_change_order_status.js?_='.$this->params->get('__v' , '1.0') , false , false);



            $doc->addScriptOptions( $this->_name , [
                // Время последнего обновления
                '__v' => $this->params->get('__v' , '1.0') ,
                'plugin' => $this->_name ,
                '__type' => $this->_type ,
                'last_update_time' => $this->params->get('last_update_time' , false) ,
                'order_live_time' => $this->params->get('order_live_time' , 3) ,
            ]);


//        $doc->addScriptDeclaration('alert("onBeforeRender2")');
        }


        /**
         * onAfterRender.
         *
         * @return  void
         *
         * @since   1.0
         */
        public function onAfterRender()
        {
            // Access to plugin parameters
            $sample = $this->params->get('sample' , '42');
        }


        /**
         * OnAfterCompress.
         *
         * @return  void
         *
         * @since   1.0
         */
        public function onAfterCompress()
        {

        }

        /**
         * onAfterRespond.
         *
         * @return  void
         *
         * @since   1.0
         */
        public function onAfterRespond()
        {

        }

        /**
         * Ajax точка входа
         *
         * @throws Exception
         * @since 3.9
         */
        public function onAjaxPlg_system_jshopping_change_order_status()
        {
            $task = $this->app->input->get('task' , false , 'STRING');
            if( !$task ) return 1;#END IF

            JLoader::registerNamespace('Plg_COS' , JPATH_PLUGINS . '/system/plg_system_jshopping_change_order_status' , $reset = false , $prepend = false , $type = 'psr4');

            $helper = new \Plg_COS\Helper($this->params);
            $result = $helper->{$task}();
            echo new JResponseJson($result);
            die();


        }

    }
