--------------- SQL ---------------

CREATE OR REPLACE FUNCTION adq.f_finalizar_reg_solicitud (
  p_administrador integer,
  p_id_usuario integer,
  p_id_funcionario_rpc integer,
  p_id_solicitud integer
)
RETURNS varchar AS
$body$
/**************************************************************************
 SISTEMA:		Adquisiciones
 FUNCION: 		adq.f_finalizar_reg_solicitud
 DESCRIPCION:   Esta funcion finaliza la solictud cambiandola al siguiente estado y asignado un RPC
 AUTOR: 		Rensi Arteaga COpar
 FECHA:	        29-1-2014
 COMENTARIOS:	
***************************************************************************
 HISTORIAL DE MODIFICACIONES:

 DESCRIPCION:	
 AUTOR:			
 FECHA:		
***************************************************************************/

DECLARE

   
    v_resp		            varchar;
    v_nombre_funcion        text;
    v_mensaje_error         text;
    
    v_id_proceso_wf integer;
    v_id_estado_wf integer;
    v_codigo_estado  varchar;
    v_id_funcionario_aprobador  integer;
    v_numero_sol  varchar;
     v_id_estado_actual  integer;
    
    
    
    va_id_tipo_estado integer [];
    va_codigo_estado varchar [];
    va_disparador varchar [];
    va_regla varchar [];
    va_prioridad integer [];
    
    
   
			    
BEGIN

/*

p_hstore


p_hstore->'id_solicitud'


*/



  v_nombre_funcion = 'adq.f_finalizar_reg_solicitud';
          
        --obtenermos datos basicos
          
          select
            s.id_proceso_wf,
            s.id_estado_wf,
            s.estado,
            s.id_funcionario_aprobador,
            s.numero
          into 
          
            v_id_proceso_wf,
            v_id_estado_wf,
            v_codigo_estado,
            v_id_funcionario_aprobador,
            v_numero_sol
            
          from adq.tsolicitud s
          where s.id_solicitud=p_id_solicitud;
          
                 
          
          
          --buscamos siguiente estado correpondiente al proceso del WF
         
          
          
        SELECT 
             ps_id_tipo_estado,
             ps_codigo_estado,
             ps_disparador,
             ps_regla,
             ps_prioridad
          into
            va_id_tipo_estado,
            va_codigo_estado,
            va_disparador,
            va_regla,
            va_prioridad
        
        FROM wf.f_obtener_estado_wf(v_id_proceso_wf, v_id_estado_wf,NULL,'siguiente');
          
          --cambiamos estado de la solicitud
          
          
        --     raise exception '% /% /%  /% /%',va_id_tipo_estado[1],va_codigo_estado[1], va_disparador[1], va_regla[1],va_prioridad[1];
          
          
          IF  va_id_tipo_estado[2] is not null  THEN
           
            raise exception 'El proceso se encuentra mal parametrizado dentro de Work Flow,  la finalizacion de solicitud solo admite un estado siguiente';
          
          END IF;
          
          IF  va_id_tipo_estado[1] is  null  THEN
           
            raise exception ' El proceso de Work Flow esta mal parametrizado, no tiene un estado siguiente para la finalizacion';
          
          END IF;
          
            
          IF  va_disparador[1]='si'  THEN
           
            raise exception ' El proceso de Work Flow esta mal parametrizado, antes de iniciar el proceso de compra necesita comprometer el presupuesto';
          
          END IF;
          
          
          --registra estado eactual en el WF
          
          
          /*
            p_id_tipo_estado_siguiente integer,
            p_id_funcionario integer,
            p_id_estado_wf_anterior integer,
            p_id_proceso_wf integer,
            p_id_usuario integer,
            p_id_depto integer = NULL::integer,
            p_obs text = ''::text,
            p_acceso_directo varchar = ''::character varying,
            p_clase varchar = NULL::character varying,
            p_parametros varchar = '{}'::character varying,
            p_tipo varchar = 'notificacion'::character varying,
            p_titulo varchar = 'Visto Bueno'::character varying
          */
          
           v_id_estado_actual =  wf.f_registra_estado_wf(va_id_tipo_estado[1], 
                                                         v_id_funcionario_aprobador, 
                                                         v_id_estado_wf, 
                                                         v_id_proceso_wf,
                                                         p_id_usuario,
                                                         NULL,
                                                         'Solicitud a espera de aprobación #'||COALESCE(v_numero_sol,'S/N'),
                                                         '../../../sis_adquisiciones/vista/solicitud/SolicitudVb.php',
                                                         'SolicitudVb');
                                                         
                                                         
                                                         
                                                         
         
        
           -- actualiza estado en la solicitud
          
           update adq.tsolicitud  s set 
             id_estado_wf =  v_id_estado_actual,
             estado = va_codigo_estado[1],
             id_funcionario_rpc=p_id_funcionario_rpc,
             id_usuario_mod=p_id_usuario,
             fecha_mod=now()
           where id_solicitud = p_id_solicitud;
           
      --Definicion de la respuesta
          v_resp = pxp.f_agrega_clave(v_resp,'mensaje','Solicitud de compra finalizada'); 
        
         

         return v_resp;

EXCEPTION
				
	WHEN OTHERS THEN
		v_resp='';
		v_resp = pxp.f_agrega_clave(v_resp,'mensaje',SQLERRM);
		v_resp = pxp.f_agrega_clave(v_resp,'codigo_error',SQLSTATE);
		v_resp = pxp.f_agrega_clave(v_resp,'procedimientos',v_nombre_funcion);
		raise exception '%',v_resp;
				        
END;
$body$
LANGUAGE 'plpgsql'
VOLATILE
CALLED ON NULL INPUT
SECURITY INVOKER
COST 100;