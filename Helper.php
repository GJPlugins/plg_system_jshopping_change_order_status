<?php

    /*******************************************************************************************************************
     *     ╔═══╗ ╔══╗ ╔═══╗ ╔════╗ ╔═══╗ ╔══╗        ╔══╗  ╔═══╗ ╔╗╔╗ ╔═══╗ ╔╗   ╔══╗ ╔═══╗ ╔╗  ╔╗ ╔═══╗ ╔╗ ╔╗ ╔════╗
     *     ║╔══╝ ║╔╗║ ║╔═╗║ ╚═╗╔═╝ ║╔══╝ ║╔═╝        ║╔╗╚╗ ║╔══╝ ║║║║ ║╔══╝ ║║   ║╔╗║ ║╔═╗║ ║║  ║║ ║╔══╝ ║╚═╝║ ╚═╗╔═╝
     *     ║║╔═╗ ║╚╝║ ║╚═╝║   ║║   ║╚══╗ ║╚═╗        ║║╚╗║ ║╚══╗ ║║║║ ║╚══╗ ║║   ║║║║ ║╚═╝║ ║╚╗╔╝║ ║╚══╗ ║╔╗ ║   ║║
     *     ║║╚╗║ ║╔╗║ ║╔╗╔╝   ║║   ║╔══╝ ╚═╗║        ║║─║║ ║╔══╝ ║╚╝║ ║╔══╝ ║║   ║║║║ ║╔══╝ ║╔╗╔╗║ ║╔══╝ ║║╚╗║   ║║
     *     ║╚═╝║ ║║║║ ║║║║    ║║   ║╚══╗ ╔═╝║        ║╚═╝║ ║╚══╗ ╚╗╔╝ ║╚══╗ ║╚═╗ ║╚╝║ ║║    ║║╚╝║║ ║╚══╗ ║║ ║║   ║║
     *     ╚═══╝ ╚╝╚╝ ╚╝╚╝    ╚╝   ╚═══╝ ╚══╝        ╚═══╝ ╚═══╝  ╚╝  ╚═══╝ ╚══╝ ╚══╝ ╚╝    ╚╝  ╚╝ ╚═══╝ ╚╝ ╚╝   ╚╝
     *------------------------------------------------------------------------------------------------------------------
     *
     * @author     Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date       16.09.2021 21:17
     * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
     * @license    GNU General Public License version 2 or later;
     ******************************************************************************************************************/

    namespace Plg_COS;
    defined('_JEXEC') or die; // No direct access to this file


    use Exception;
    use JDatabaseDriver;
    use Joomla\CMS\Application\CMSApplication;
    use Joomla\CMS\Factory;
    use Joomla\Registry\Registry;
    use jshopOrderChangeStatus;

    /**
     * Class Helper
     *
     * @package Plg_COS
     * @since   3.9
     * @auhtor  Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date    16.09.2021 21:17
     *
     */
    class Helper
    {

        /**
         * @var CMSApplication|null
         * @since 3.9
         */
        protected $app;
        /**
         * @var JDatabaseDriver|null
         * @since 3.9
         */
        protected $db;
        /**
         * Параметры плагина
         * @var array|object|Registry
         * @since 3.9
         */
        protected $params;
        /**
         * Количество заказов у которых изменен статус заказа
         * @var int
         * @since 3.9
         */
        protected $orderIsUpd = 0 ;
        /**
         * @var string Sql Data Time
         * @since 3.9
         */
        private $now;

        /**
         * Helper constructor.
         *
         * @param $params array|object|Registry
         *
         * @throws Exception
         * @since 3.9
         */
        public function __construct( $params = [] )
        {
            $this->app = Factory::getApplication();
            $this->db = Factory::getDbo();
            $this->params = $params ;

            try
            {
                \JLoader::registerNamespace( 'GNZ11' , JPATH_LIBRARIES . '/GNZ11' , $reset = false , $prepend = false , $type = 'psr4' );
                $GNZ11_js =  \GNZ11\Core\Js::instance();
            }
            catch( Exception $e )
            {
                if( !\Joomla\CMS\Filesystem\Folder::exists( $this->patchGnz11 ) && $this->app->isClient('administrator') )
                {
                    $this->app->enqueueMessage('Должна быть установлена библиотека GNZ11' , 'error');
                }#END IF
            }

            $_data= new \JDate();
            $this->now = $_data->toSql();

            return $this;
        }

        /**
         * Смена статуса заказа AJAX
         * @return bool|void
         * @since  3.9
         * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
         * @date   16.09.2021 23:05
         *
         */
        public function updOrderStatus(){

            \JTable::addIncludePath(JPATH_COMPONENT_SITE.'/tables');
            \JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_jshopping/models');

            require_once(JPATH_ROOT."/components/com_jshopping/lib/factory.php");
            require_once(JPATH_ROOT.'/administrator/components/com_jshopping/functions.php');

            if( $this->params->get('debug_on' , false ) )
            {
                $this->app->enqueueMessage('<i>Сообщения можно отключить в настройках плагина "Отладка"</i>');
            }#END IF

            /**
             * @var $model jshopOrderChangeStatus
             */
            $model = \JSFactory::getModel('orderChangeStatus', 'jshop');
//            $model = \JModelLegacy::getInstance('orderChangeStatus', 'jshop' , []);
            $ordersArr = $this->getOrder();

            $this->updLastTime();

            if( empty( $ordersArr )  ) {
                $this->app->enqueueMessage('Нет заказов для изменения статуса');
                return true ;
            } #END IF

            foreach ( $ordersArr as $order )
            {

                $status = $this->params->get('order_status_new' , 3 ) ;
                $sendMessage = $this->params->get('customer_notify_status_new' , 1 ) ;
                $status_id = '' ;
                $notify = 1 ;
                $comments = $this->params->get('customer_notify_comments' , "Changed automatically plugin - Default" ) ;
                $include = $this->params->get('include_notify_comments' , 0 )  ;
                $view_order = false;

                $model->setData($order->order_id , $status, $sendMessage, $status_id, $notify, $comments, $include, $view_order);
                $model->setAppAdmin(1);
                $model->store();
                $this->orderIsUpd++;


//                $this->updateOrderStatus( $order );
//                $this->updateOrderHistory( $order );
                # Если Установлено оповещать клиента
                /*if( $this->params->get('customer_notify_status_new' , 1 ) )
                {


//                    echo'<pre>';print_r( $model );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;
//
//                    die(__FILE__ .' '. __LINE__ );




                    // ..............
                }#END IF*/
            }#END FOREACH




            $titles = array('Обновлен %d заказ', 'Обновлено %d заказа', 'Обновлено %d заказов');
            $txt = \GNZ11\Document\Text::declOfNum ( $this->orderIsUpd , $titles );
            $this->app->enqueueMessage( $txt );



            return true ;





        }

        /**
         * Дообавить в историю заказа
         * @since  3.9
         * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
         * @date   16.09.2021 23:41
         *
         */
        protected function updateOrderHistory($order){



            $Query = $this->db->getQuery(true);
            $table = $this->db->quoteName('#__jshopping_order_history') ;
            $columns = array('order_id','order_status_id','status_date_added','customer_notify' ,'comments' );
            $values =
                $this->db->quote( $order->order_id ).","
                .$this->db->quote( $this->params->get('order_status_new' , 3 ) ).","
                .$this->db->quote(  $this->now ).","
                .$this->db->quote(  $this->params->get('customer_notify_status_new' , 1 ) ) .","
                .$this->db->quote(  $this->params->get('customer_notify_comments' , '' ) ) ;

            $Query->values(  $values );
            $Query->insert( $table )
                ->columns( $this->db->quoteName( $columns ) );

            $this->db->setQuery($Query);
//            echo $Query->dump();

            $this->db->execute();
        }

        /***
         * Изменение статуса заказа + добавление в историю статуса
         * @param $order
         *
         * @since  3.9
         * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
         * @date   16.09.2021 23:22
         *
         */
        protected function updateOrderStatus( $order ){
            $Query = $this->db->getQuery(true);
            // Условия обновления
            $conditions = array(
                $this->db->quoteName('order_id') . ' = '  . $this->db->quote( $order->order_id )
            );

            $setArr = [
                $this->db->quoteName('order_status') . ' = ' . $this->params->get('order_status_new' , 3 ) ,
            ];

            $Query->update($this->db->quoteName('#__jshopping_orders'))
                ->set( $setArr  )
                ->where($conditions);
//              echo $Query->dump();

            // Устанавливаем и выполняем запрос
            $this->db->setQuery($Query);
            $this->db->execute();
            // Количество затронутых полей
            $this->orderIsUpd += $this->db->getAffectedRows();
        }

        /**
         * Получить заказы для смены статуса
         * @return array|mixed
         * @since  3.9
         * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
         * @date   16.09.2021 23:21
         *
         */
        protected function getOrder(){
            $liveTime = $this->params->get('order_live_time') * 60 ;
            $time = time() - $liveTime;
            $dateSql = date('Y-m-d H:i:s' , $time )   ;

            $Query = $this->db->getQuery(true) ;
            $select = [
                '*',
                $this->db->quoteName('order_id'),
                $this->db->quoteName('order_number'),
            ];
            $Query->select( $select )
//                ->from( $this->db->quoteName('#__jshopping_order_history'));
                ->from( $this->db->quoteName('#__jshopping_orders'));
            $where = [
                $this->db->quoteName('order_status') .'=' . $this->db->quote('1'),
                $this->db->quoteName('order_date') .' < '.$this->db->quote( $dateSql )
            ];
            $Query->where($where);
            # $Query->order( ' ASC' );
            # $Query->order( ' DESC' );
            $this->db->setQuery( $Query ) ;
//            echo $Query->dump();
            return $this->db->loadObjectList();
        }

        /**
         * Обновить в параметрах плагина дату последнего запуска
         * @since  3.9
         * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
         * @date   17.09.2021 01:15
         *
         */
        protected function updLastTime(){

            $this->params->set('last_update_time' , $this->now );
            $jsonStr = $this->params->toString();


            $Query = $this->db->getQuery(true) ;

            // Условия обновления
            $conditions = array(
                $this->db->quoteName('element') . ' = '  . $this->db->quote( 'plg_system_jshopping_change_order_status' )
            );



            $Query->update($this->db->quoteName('#__extensions'))
                ->set( $this->db->quoteName('params') . '=' .   $this->db->quote( $jsonStr ) )
                ->where($conditions);
                //  echo $Query->dump();

            // Устанавливаем и выполняем запрос
            $this->db->setQuery($Query);
            $this->db->execute();

        }
    }




















