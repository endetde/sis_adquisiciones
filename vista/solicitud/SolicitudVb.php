<?php
/**
*@package pXP
*@file gen-SistemaDist.php
*@author  (fprudencio)
*@date 20-09-2011 10:22:05
*@description Archivo con la interfaz de usuario que permite 
*dar el visto a solicitudes de compra
*
*/
header("content-type: text/javascript; charset=UTF-8");
?>
<script>
Phx.vista.SolicitudVb = {
    bedit:false,
    bnew:false,
    bsave:false,
    bdel:false,
	require: '../../../sis_adquisiciones/vista/solicitud/Solicitud.php',
	requireclase: 'Phx.vista.Solicitud',
	title: 'Solicitud',
	nombreVista: 'SolicitudVb',
	
	constructor: function(config) {
	    
	    this.maestro=config.maestro;
	    
	    this.Atributos[this.getIndAtributo('id_funcionario')].form=false;
        this.Atributos[this.getIndAtributo('id_funcionario_aprobador')].form=false;
        this.Atributos[this.getIndAtributo('id_moneda')].form=false;
        //this.Atributos[this.getIndAtributo('id_proceso_macro')].form=false;
        this.Atributos[this.getIndAtributo('fecha_soli')].form=false;
        this.Atributos[this.getIndAtributo('id_categoria_compra')].form=false;
        this.Atributos[this.getIndAtributo('id_uo')].form=false;
        this.Atributos[this.getIndAtributo('id_depto')].form=false;
        this.Atributos[this.getIndAtributo('revisado_asistente')].grid=true; 
        
        //funcionalidad para listado de historicos
        this.historico = 'no';
        this.tbarItems = ['-',{
            text: 'Histórico',
            enableToggle: true,
            pressed: false,
            toggleHandler: function(btn, pressed) {
               
                if(pressed){
                    this.historico = 'si';
                     this.desBotoneshistorico();
                }
                else{
                   this.historico = 'no' 
                }
                
                this.store.baseParams.historico = this.historico;
                this.reload();
             },
            scope: this
           }];
        
    	Phx.vista.SolicitudVb.superclass.constructor.call(this,config);
    	this.addButton('ini_estado',{  argument: {estado: 'inicio'},text:'Dev. al Solicitante',iconCls: 'batras',disabled:true,handler:this.antEstado,tooltip: '<b>Retorna la Solcitud al estado borrador</b>'});
        this.addButton('ant_estado',{ argument: {estado: 'anterior'},text:'Rechazar',iconCls: 'batras',disabled:true,handler:this.antEstado,tooltip: '<b>Pasar al Anterior Estado</b>'});
        this.addButton('sig_estado',{ text:'Aprobar', iconCls: 'badelante', disabled: true, handler: this.sigEstado, tooltip: '<b>Pasar al Siguiente Estado</b>'});
        
                
        this.store.baseParams={tipo_interfaz:this.nombreVista};
        //coloca filtros para acceso directo si existen
        if(config.filtro_directo){
           this.store.baseParams.filtro_valor = config.filtro_directo.valor;
           this.store.baseParams.filtro_campo = config.filtro_directo.campo;
        }
        //carga inicial de la pagina
        this.load({params:{start:0, limit:this.tam_pag}}); 
        
        
        
        if(this.nombreVista == 'solicitudvbpoa') {
           this.addButton('obs_poa',{ text:'Datos POA', disabled:true, handler: this.initObs, tooltip: '<b>Código de actividad POA</b>'});
           this.crearFormObs();
        }
        if(this.nombreVista == 'solicitudvbpresupuestos') {
           this.addButton('obs_presu',{text:'Obs. Presupuestos', disabled:true, handler: this.initObs, tooltip: '<b>Observacioens del área de presupuesto</b>'});
           this.crearFormObs();
        }
        
		console.log('configuracion',config, this.nombreVista)
	},
	
   
   
     
  //deshabilitas botones para informacion historica
  desBotoneshistorico:function(){
      
      this.getBoton('ant_estado').disable();
      this.getBoton('sig_estado').disable();
      this.getBoton('ini_estado').disable();
      
  },
  preparaMenu:function(n){
      var data = this.getSelectedData();
      var tb =this.tbar;
      Phx.vista.SolicitudVb.superclass.preparaMenu.call(this,n);  
      
      if(this.historico == 'no'){
          if(data.estado =='aprobado' ){ 
            this.getBoton('ant_estado').enable();
            this.getBoton('sig_estado').disable();
            this.getBoton('ini_estado').enable();
            }
            if(data.estado =='proceso'){
                this.getBoton('ant_estado').disable();
                this.getBoton('sig_estado').disable();
                this.getBoton('ini_estado').disable();
            }
            
            if(data.estado =='anulado' || data.estado =='finalizado'){
                this.getBoton('ant_estado').disable();
                this.getBoton('sig_estado').disable();
                this.getBoton('ini_estado').disable();
            }
            
            if(data.estado !='aprobado' && data.estado !='proceso' &&data.estado !='anulado' && data.estado !='finalizado' ){
                this.getBoton('ant_estado').enable();
                this.getBoton('sig_estado').enable();
                this.getBoton('ini_estado').enable();
            }
            
             if(data.estado =='vbpoa'){
             	if(this.getBoton('obs_poa')){
             		this.getBoton('obs_poa').enable();
             	}
             }
             if(this.getBoton('obs_presu')){
               this.getBoton('obs_presu').enable();
             }
             
           //habilitar reporte de colicitud de comrpa y preorden de compra
           this.menuAdq.enable();
      } 
      else{
          this.desBotoneshistorico();
          if(this.getBoton('obs_poa')){
             this.getBoton('obs_poa').enable();
          }
          if(this.getBoton('obs_presu')){
             this.getBoton('obs_presu').disable();
          }
          
      }   
      return tb 
     }, 
     liberaMenu:function(){
        var tb = Phx.vista.SolicitudVb.superclass.liberaMenu.call(this);
        if(tb){
            this.getBoton('sig_estado').disable();
            this.getBoton('ini_estado').disable();
            this.getBoton('ant_estado').disable();
            if(this.getBoton('obs_poa')){
              this.getBoton('obs_poa').disable();
            }
            if(this.getBoton('obs_presu')){
              this.getBoton('obs_presu').disable();
            }
            //boton de reporte de solicitud y preorden de compra
            this.menuAdq.disable();
           
        }
        return tb
    },  
    
    checkPresupuesto:function(){
    	var d = this.getSelectedData();
	    Phx.CP.loadingShow();
        Ext.Ajax.request({
            // form:this.form.getForm().getEl(),
            url:'../../sis_adquisiciones/control/Solicitud/checkPresupuesto',
            params:{id_solicitud:d.id_solicitud, id_funcionario:d.id_funcionario},
            success:this.successCheck,
            failure: this.conexionFailure,
            timeout:this.timeout,
            scope:this
        }); 
    	
    },  
    
    successCheck:function(resp){
            
            Phx.CP.loadingHide();
            var reg = Ext.util.JSON.decode(Ext.util.Format.trim(resp.responseText));
            if(!reg.ROOT.error){
            	this.reload();
            }
    },
    
    sigEstado:function(){                   
      	var rec=this.sm.getSelected();
      	
      	this.mostrarWizard(rec);
      	
               
     },
    mostrarWizard : function(rec) {
     	var configExtra = [],
     		obsValorInicial;
     	 if(this.nombreVista == 'solicitudvbpresupuestos') {
     	 	obsValorInicial = rec.data.obs_presupuestos;
     	 }
     	
     	if(rec.data.estado == 'vbrpc'){
     		 configExtra = [
					       	{
					   			config:{
				                    name: 'instruc_rpc',
				                    fieldLabel:'Intrucciones',
				                    allowBlank: false,
				                    emptyText:'Tipo...',
				                    typeAhead: true,
				                    triggerAction: 'all',
				                    lazyRender: true,
				                    mode: 'local',
				                    valueField: 'estilo',
				                    gwidth: 100,
				                    value: 'Orden de Bien/Servicio',
				                    store: ['Iniciar Contrato','Orden de Bien/Servicio','Cotizar']
				                },
					   			type:'ComboBox',
					   			id_grupo: 1,
					   			form: true
					       	}
					     ];
     	 }
     	 
     	
     	this.objWizard = Phx.CP.loadWindows('../../../sis_workflow/vista/estado_wf/FormEstadoWf.php',
                                'Estado de Wf',
                                {
                                    modal: true,
                                    width: 700,
                                    height: 450
                                }, {
                                	configExtra: configExtra,
                                	data:{
                                       id_estado_wf: rec.data.id_estado_wf,
                                       id_proceso_wf: rec.data.id_proceso_wf,
                                       fecha_ini: rec.data.fecha_tentativa,
                                       url_verificacion:'../../sis_adquisiciones/control/Solicitud/verficarSigEstSolWf'
                                   },
                                   obsValorInicial : obsValorInicial,
                                }, this.idContenedor,'FormEstadoWf',
                                {
                                    config:[{
                                              event:'beforesave',
                                              delegate: this.onSaveWizard,
                                              
                                            },
					                        {
					                          event:'requirefields',
					                          delegate: function () {
						                          	this.onButtonEdit();
										        	this.window.setTitle('Registre los campos antes de pasar al siguiente estado');
										        	this.formulario_wizard = 'si';
					                          }
					                          
					                        }],
                                    
                                    scope:this
                                 });        
     },
    onSaveWizard:function(wizard,resp){
        Phx.CP.loadingShow();
        Ext.Ajax.request({
            url:'../../sis_adquisiciones/control/Solicitud/siguienteEstadoSolicitudWzd',
            params:{
            	    id_proceso_wf_act:  resp.id_proceso_wf_act,
	                id_estado_wf_act:   resp.id_estado_wf_act,
	                id_tipo_estado:     resp.id_tipo_estado,
	                id_funcionario_wf:  resp.id_funcionario_wf,
	                id_depto_wf:        resp.id_depto_wf,
	                obs:                resp.obs,
	                instruc_rpc:		resp.instruc_rpc,
	                json_procesos:      Ext.util.JSON.encode(resp.procesos)
                },
            success: this.successWizard,
            failure: this.failureCheck, //chequea si esta en verificacion presupeusto para enviar correo de transferencia
            argument: { wizard:wizard },
            timeout: this.timeout,
            scope: this
        });
    },
    successWizard:function(resp){
        Phx.CP.loadingHide();
        resp.argument.wizard.panel.destroy()
        this.reload();
    },
    
    failureCheck:function(resp1,resp2,resp3){
       	  
       	   this.conexionFailure(resp1,resp2,resp3);
       	   var d= this.sm.getSelected().data;
       	   if(d.estado == 'vbpresupuestos'){
       	   	 this.checkPresupuesto();
       	   }
       	  
       },
       
    //para retroceder de estado
    antEstado:function(res){
         var rec=this.sm.getSelected(),
             obsValorInicial;
         
         if(this.nombreVista == 'solicitudvbpresupuestos') {
     	 	obsValorInicial = rec.data.obs_presupuestos;
     	 }
         Phx.CP.loadWindows('../../../sis_workflow/vista/estado_wf/AntFormEstadoWf.php',
            'Estado de Wf',
            {
                modal:true,
                width:450,
                height:250
                
            }, { data:rec.data, 
            	 estado_destino: res.argument.estado,
                 obsValorInicial: obsValorInicial }, this.idContenedor,'AntFormEstadoWf',
            {
                config:[{
                          event:'beforesave',
                          delegate: this.onAntEstado,
                        }
                        ],
               scope:this
             })
   },
   
   onAntEstado: function(wizard,resp){
            Phx.CP.loadingShow();
            var operacion = 'cambiar';
            operacion=  resp.estado_destino == 'inicio'?'inicio':operacion; 
            Ext.Ajax.request({
                url:'../../sis_adquisiciones/control/Solicitud/anteriorEstadoSolicitud',
                params:{
                        id_proceso_wf: resp.id_proceso_wf,
                        id_estado_wf:  resp.id_estado_wf,  
                        obs: resp.obs,
                        operacion: operacion,
                        id_solicitud: resp.data.id_solicitud
                 },
                argument:{wizard:wizard},  
                success: this.successEstadoSinc,
                failure: this.conexionFailure,
                timeout: this.timeout,
                scope: this
            });
           
     },
     
   successEstadoSinc:function(resp){
        Phx.CP.loadingHide();
        resp.argument.wizard.panel.destroy()
        this.reload();
     },
    crearFormObs:function(){
		
		 var titulo;
		 if(this.nombreVista == 'solicitudvbpoa') {
		 	   titulo = 'Datos POA';
				this.formObs = new Ext.form.FormPanel({
		            baseCls: 'x-plain',
		            autoDestroy: true,
		            border: false,
		            layout: 'form',
		            autoHeight: true,
		            items: [
		                 {
                             name: 'codigo_poa',
                             xtype: 'awesomecombo',
                             fieldLabel: 'Código POA',
                             allowBlank: false,
                             emptyText : 'Actividad...',
                             store : new Ext.data.JsonStore({
                                 url : '../../sis_presupuestos/control/Objetivo/listarObjetivo',
                                 id : 'codigo',
                                 root : 'datos',
                                 sortInfo : {
                                     field : 'codigo',
                                     direction : 'ASC'
                                 },
                                 totalProperty : 'total',
                                 fields : ['codigo', 'descripcion','sw_transaccional','detalle_descripcion'],
                                 remoteSort : true,
                                 baseParams : {
                                     par_filtro : 'obj.codigo#obj.descripcion'
                                 }
                             }),
                             valueField : 'codigo',
                             displayField : 'detalle_descripcion',
                             forceSelection : true,
                             typeAhead : false,
                             triggerAction : 'all',
                             lazyRender : true,
                             mode : 'remote',
                             pageSize : 10,
                             queryDelay : 1000,
                             gwidth : 150,
                             minChars : 2,
                             enableMultiSelect:true
		                },
		                 
		                 {
		                    name: 'obs_poa',
		                    xtype: 'textarea',
		                    fieldLabel: 'Obs POA',
		                    allowBlank: true,
		                    grow: true,
		                    growMin : '80%',
		                    value:'',
		                    anchor: '80%',
		                    maxLength:500
		                }]
		        });
       }
       else{
       	 titulo = 'Observaciones Presupuestos';
       	 this.formObs = new Ext.form.FormPanel({
            baseCls: 'x-plain',
            autoDestroy: true,
            border: false,
            layout: 'form',
            autoHeight: true,
            items: [
                 {
                    name: 'obs',
                    xtype: 'textarea',
                    fieldLabel: 'Obs',
                    allowBlank: true,
                    grow: true,
                    growMin : '80%',
                    value:'',
                    anchor: '80%',
                    maxLength:500
                }]
        });
        
       }
        
        
       
         this.wObs = new Ext.Window({
            title: titulo,
            collapsible: true,
            maximizable: true,
            autoDestroy: true,
            width: 380,
            height: 290,
            layout: 'fit',
            plain: true,
            bodyStyle: 'padding:5px;',
            buttonAlign: 'center',
            items: this.formObs,
            modal:true,
            closeAction: 'hide',
            buttons: [{
                    text: 'Guardar',
                    handler: this.submitObs,
                    scope: this
                    
             },
             {
                text: 'Cancelar',
                handler:function(){this.wObs.hide()},
                scope:this
            }]
        });
        if(this.nombreVista == 'solicitudvbpoa') { 
        	this.cmbObsPoa = this.formObs.getForm().findField('obs_poa');
        	this.cmbCodigoPoa = this.formObs.getForm().findField('codigo_poa');
        }
        else{
        	this.cmbObsPres = this.formObs.getForm().findField('obs');
        }
	},
	
	initObs:function(){
		var d= this.sm.getSelected().data;
		if(this.nombreVista == 'solicitudvbpoa') { 
	        this.cmbObsPoa.setValue(d.obs_poa);
            this.cmbCodigoPoa.store.baseParams.id_gestion = d.id_gestion;
            this.cmbCodigoPoa.store.baseParams.sw_transaccional = 'movimiento';

	        this.cmbCodigoPoa.setValue(d.codigo_poa);
       }
       else{
       	   this.cmbObsPres.setValue(d.obs_presupuestos);
       }
		this.wObs.show()
	},
	
	submitObs:function(){
		    Phx.CP.loadingShow();
		    var d= this.sm.getSelected().data,
		        url, params;
            if(this.nombreVista == 'solicitudvbpoa') {
               url = '../../sis_adquisiciones/control/Solicitud/modificarObsPoa';
               params = {
                    id_solicitud:d.id_solicitud,
                    obs_poa: this.cmbObsPoa.getValue(),
                    codigo_poa: this.cmbCodigoPoa.getValue()
                    };
            }
            else{
            	 url = '../../sis_adquisiciones/control/Solicitud/modificarObsPresupuestos';
            	 params= { id_solicitud:d.id_solicitud,
                           obs: this.cmbObsPres.getValue()
                        };
            }
            Ext.Ajax.request({
                url: url,
                params: params,
                success: function(resp){
                           Phx.CP.loadingHide();
			               var reg = Ext.util.JSON.decode(Ext.util.Format.trim(resp.responseText));
			               if(!reg.ROOT.error){
			            	  this.reload();
                              this.wObs.hide();
			               }
			             },
                failure: function(resp1,resp2,resp3){
       	  
			       	   this.conexionFailure(resp1,resp2,resp3);
			       	   var d = this.sm.getSelected().data;
			       	   
			       	  
			       },
                timeout:this.timeout,
                scope:this
            }); 
		
	},
	
	south:
          { 
          url:'../../../sis_adquisiciones/vista/solicitud_det/SolicitudReqDet.php',
          title:'Detalle', 
          height:'50%',
          cls:'SolicitudReqDet'
         }
};
</script>
