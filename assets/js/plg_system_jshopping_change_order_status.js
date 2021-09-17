/*******************************************************************************************************************
 *     ╔═══╗ ╔══╗ ╔═══╗ ╔════╗ ╔═══╗ ╔══╗        ╔══╗  ╔═══╗ ╔╗╔╗ ╔═══╗ ╔╗   ╔══╗ ╔═══╗ ╔╗  ╔╗ ╔═══╗ ╔╗ ╔╗ ╔════╗
 *     ║╔══╝ ║╔╗║ ║╔═╗║ ╚═╗╔═╝ ║╔══╝ ║╔═╝        ║╔╗╚╗ ║╔══╝ ║║║║ ║╔══╝ ║║   ║╔╗║ ║╔═╗║ ║║  ║║ ║╔══╝ ║╚═╝║ ╚═╗╔═╝
 *     ║║╔═╗ ║╚╝║ ║╚═╝║   ║║   ║╚══╗ ║╚═╗        ║║╚╗║ ║╚══╗ ║║║║ ║╚══╗ ║║   ║║║║ ║╚═╝║ ║╚╗╔╝║ ║╚══╗ ║╔╗ ║   ║║
 *     ║║╚╗║ ║╔╗║ ║╔╗╔╝   ║║   ║╔══╝ ╚═╗║        ║║─║║ ║╔══╝ ║╚╝║ ║╔══╝ ║║   ║║║║ ║╔══╝ ║╔╗╔╗║ ║╔══╝ ║║╚╗║   ║║
 *     ║╚═╝║ ║║║║ ║║║║    ║║   ║╚══╗ ╔═╝║        ║╚═╝║ ║╚══╗ ╚╗╔╝ ║╚══╗ ║╚═╗ ║╚╝║ ║║    ║║╚╝║║ ║╚══╗ ║║ ║║   ║║
 *     ╚═══╝ ╚╝╚╝ ╚╝╚╝    ╚╝   ╚═══╝ ╚══╝        ╚═══╝ ╚═══╝  ╚╝  ╚═══╝ ╚══╝ ╚══╝ ╚╝    ╚╝  ╚╝ ╚═══╝ ╚╝ ╚╝   ╚╝
 *------------------------------------------------------------------------------------------------------------------
 * @author Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
 * @date 16.09.2021 20:44
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 ******************************************************************************************************************/
/* global jQuery , Joomla   */
window.plg_system_jshopping_change_order_status = function () {
    var $ = jQuery;
    var self = this;
    // Домен сайта
    var host = Joomla.getOptions( 'GNZ11' ).Ajax.siteUrl;
    // Медиа версия
    var __v = '';

    this.__type = false;
    this.__plugin = false;
    this.__name = false;
    this._params = {
        __v : null ,    // version str
        _v  : null ,     // md5
        debug_on  : 0 ,  // Режим отладки
    };
    this._params.last_update_time = false;



    // Параметры Ajax по умолчанию
    this.AjaxDefaultData = {
        template : null ,
        group    : null ,
        plugin   : null ,
        module   : null ,
        method   : null ,
        option   : 'com_ajax' ,
        format   : 'json' ,
        task     : null ,
    };
    // Default object parameters
    this.ParamsDefaultData = {
        // Медиа версия
        __v : '1.0.0' ,
        // Режим разработки
        development_on : false ,
    }

    /**
     * Start Init
     * @constructor
     */
    this.Init = function () {
        this._params = Joomla.getOptions( 'plg_system_jshopping_change_order_status' , this.ParamsDefaultData );
        __v = self._params.development_on ? '' : '?v=' + self._params.__v;
        // Параметры Ajax Default
        this.setAjaxDefaultData();
        // Добавить слушателей событий
        // this.addEvtListener();


        console.log( this.AjaxDefaultData );

        this.Start();

    }
    /**
     * Добавить слушателей событий
     */
    this.addEvtListener = function () {
        /**
         * ex.Tag : <div contenteditable data-evt-action="onKeydownActions__header__title"  >Заказать звонок</button>
         */
        document.addEventListener( 'keydown' , function ( evt ) {
            switch ( evt.target.dataset.evtAction )
            {
                case 'onKeydownActions__header__title' :
                    // self.Actions__header.title(evt);
                    // CollBack method

                    break;
                default :
                    return;
            }
        } );
        /**
         * ex.Tag : <button data-evt-action="call-me-back" type="button" >Заказать звонок</button>
         */
        document.addEventListener( 'click' , function ( evt ) {
            switch ( evt.target.dataset.evtAction )
            {
                case 'call-me-back' :
                    // CollBack method
                    break;
                default :
                    return;
            }
        } , { passive : true } );
    };



    this.Start = function (){
        this.updOrderStatus();


        console.log( this._params )
    }

    this.updOrderStatus = function (){
        var Data = {
            task : 'updOrderStatus',
        }
        this.AjaxPost( Data ).then(function ( r ) {
            if ( r.data ){
                if ( r.messages ){
                    if ( typeof r.messages.message !== 'undefined' && self._params.debug_on ){
                        for ( var i = 0 ; i< r.messages.message.length ; i++ ){
                            var message = {};
                            message.txt = r.messages.message[i];
                            self.messageNoty(message);
                        }
                    }

                }

            }
            console.log('plg_system_jshopping_change_order_status:->r >>> ' , r.data );

        },function ( err ) {console.log('plg_system_jshopping_change_order_status:->err >>> ' , err );});
    }

    /**
     * Отправить запрос
     * @param Data - отправляемые данные
     * Должен содержать Data.task = 'taskName';
     * @returns {Promise}
     * @constructor
     */
    this.AjaxPost = function ( Data ) {
        var data = $.extend( true , this.AjaxDefaultData , Data );
        return new Promise( function ( resolve , reject ) {
            self.getModul( "Ajax" ).then( function ( Ajax ) {
                // Не обрабатывать сообщения
                Ajax.ReturnRespond = true;
                // Отправить запрос
                Ajax.send( data , self._params.__name ).then( function ( r ) {
                    resolve( r );
                } , function ( err ) {
                    console.error( err );
                    reject( err );
                } )
            } );
        } );
    };
    /**
     * Отображение сообщения Noty
     * @param message
     * @use :
     *          var message = {};
     *          message.txt = 'Plugin Start';
     *          self.messageNoty(message);
     */
    this.messageNoty = function ( message ){
        var param = {
            type: 'info',            // Тип сообщений - alert, success, warning, error, info/information
            layout:'bottomRight' ,   // Позиция вывода top, topLeft, topCenter, topRight, center, centerLeft, centerRight,
                                     // bottom, bottomLeft, bottomCenter, bottomRight
            timeout : 10000  ,       // Время отображения
        }
        self.__loadModul.Noty(param).then(function(Noty){
            Noty.options.text = message.txt ;
            Noty.show();
        })
        console.log('this.messageNoty >>> ' , message.txt );
    }
    /**
     * Параметры Ajax Default
     */
    this.setAjaxDefaultData = function () {
        /**
         * Название модуля или плагина
         * @type {string}
         * @private
         */
        var __name = ''
        /**
         * Группа плагинов
         * @type {string}
         * @private
         */
        var __type = ''

        if ( typeof this._params.__name !== "undefined" )
        {
            __name = this._params.__name;
        }
        else if ( typeof this._params.plugin !== "undefined" )
        {
            __name = this._params.plugin;
        }

        if ( typeof this._params.__type !== "undefined" )
        {
            __type = this._params.__type;
        }
        else if ( typeof this._params.group !== "undefined" )
        {
            __type = this._params.group;
        }

        this.AjaxDefaultData.group = __type;
        this.AjaxDefaultData.plugin = __name;
        this.AjaxDefaultData.module = this._params.__module;
        this._params.__name = this._params.__name || this._params.__module;
    }
    this.Init();
};
/* global GNZ11 */
window.plg_system_jshopping_change_order_status.prototype = new GNZ11();
new window.plg_system_jshopping_change_order_status();
/*--------------------------------------------------------------------------------------------------------------------*/
/**
 * Прототип - Позволяет добавлять элементы из вновь созданных  элементов
 * https://stackoverflow.com/a/32135318/6665363
 * @param element
 */
Element.prototype.appendAfter = function ( element ) {
    element.parentNode.insertBefore( this , element.nextSibling );
};
Element.prototype.appendBefore = function ( element ) {
    element.parentNode.insertBefore( this , element );
};
