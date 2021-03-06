--------------- SQL ---------------

CREATE OR REPLACE FUNCTION adq.ft_rpc_uo_log_sel (
  p_administrador integer,
  p_id_usuario integer,
  p_tabla varchar,
  p_transaccion varchar
)
RETURNS varchar AS
$body$
/**************************************************************************
 SISTEMA:		Adquisiciones
 FUNCION: 		adq.ft_rpc_uo_log_sel
 DESCRIPCION:   Funcion que devuelve conjuntos de registros de las consultas relacionadas con la tabla 'adq.trpc_uo_log'
 AUTOR: 		 (admin)
 FECHA:	        03-06-2014 13:14:39
 COMENTARIOS:	
***************************************************************************
 HISTORIAL DE MODIFICACIONES:

 DESCRIPCION:	
 AUTOR:			
 FECHA:		
***************************************************************************/

DECLARE

	v_consulta    		varchar;
	v_parametros  		record;
	v_nombre_funcion   	text;
	v_resp				varchar;
			    
BEGIN

	v_nombre_funcion = 'adq.ft_rpc_uo_log_sel';
    v_parametros = pxp.f_get_record(p_tabla);

	/*********************************    
 	#TRANSACCION:  'ADQ_RPCL_SEL'
 	#DESCRIPCION:	Consulta de datos
 	#AUTOR:		admin	
 	#FECHA:		03-06-2014 13:14:39
	***********************************/

	if(p_transaccion='ADQ_RPCL_SEL')then
     				
    	begin
    		--Sentencia de la consulta
			v_consulta:='select 
                        rpcl.id_rpc_uo_log,
						rpcl.monto_max,
						rpcl.id_rpc_uo,
						rpcl.id_categoria_compra,
						rpcl.operacion,
						rpcl.fecha_ini,
						rpcl.descripcion,
						rpcl.fecha_fin,
						rpcl.id_rpc,
						rpcl.id_cargo,
						rpcl.id_cargo_ai,
						rpcl.id_uo,
						rpcl.estado_reg,
						rpcl.ai_habilitado,
						rpcl.monto_min,
						rpcl.id_usuario_ai,
						rpcl.fecha_reg,
						rpcl.usuario_ai,
						rpcl.id_usuario_reg,
						rpcl.fecha_mod,
						rpcl.id_usuario_mod,
						usu1.cuenta as usr_reg,
						usu2.cuenta as usr_mod,
                        ca.nombre as desc_cargo,
                        cai.nombre as desc_cargo_ai,
                        uo.nombre_unidad as desc_uo,
                        cat.nombre as desc_categoria_compra
						from adq.trpc_uo_log rpcl
						inner join segu.tusuario usu1 on usu1.id_usuario = rpcl.id_usuario_reg
						left join segu.tusuario usu2 on usu2.id_usuario = rpcl.id_usuario_mod
                        inner join orga.tcargo ca on ca.id_cargo = rpcl.id_cargo
                        left join orga.tcargo cai on cai.id_cargo = rpcl.id_cargo_ai
                        left join orga.tuo  uo on uo.id_uo = rpcl.id_uo
                        left join adq.tcategoria_compra cat on cat.id_categoria_compra = rpcl.id_categoria_compra
				        where  ';
			
			--Definicion de la respuesta
			v_consulta:=v_consulta||v_parametros.filtro;
			v_consulta:=v_consulta||' order by ' ||v_parametros.ordenacion|| ' ' || v_parametros.dir_ordenacion || ' limit ' || v_parametros.cantidad || ' offset ' || v_parametros.puntero;

			--Devuelve la respuesta
			return v_consulta;
						
		end;

	/*********************************    
 	#TRANSACCION:  'ADQ_RPCL_CONT'
 	#DESCRIPCION:	Conteo de registros
 	#AUTOR:		admin	
 	#FECHA:		03-06-2014 13:14:39
	***********************************/

	elsif(p_transaccion='ADQ_RPCL_CONT')then

		begin
			--Sentencia de la consulta de conteo de registros
			v_consulta:='select count(id_rpc_uo_log)
					    from adq.trpc_uo_log rpcl
						inner join segu.tusuario usu1 on usu1.id_usuario = rpcl.id_usuario_reg
						left join segu.tusuario usu2 on usu2.id_usuario = rpcl.id_usuario_mod
                        inner join orga.tcargo ca on ca.id_cargo = rpcl.id_cargo
                        left join orga.tcargo cai on cai.id_cargo = rpcl.id_cargo_ai
                        left join orga.tuo  uo on uo.id_uo = rpcl.id_uo
                        left join adq.tcategoria_compra cat on cat.id_categoria_compra = rpcl.id_categoria_compra
					    where ';
			
			--Definicion de la respuesta		    
			v_consulta:=v_consulta||v_parametros.filtro;

			--Devuelve la respuesta
			return v_consulta;

		end;
					
	else
					     
		raise exception 'Transaccion inexistente';
					         
	end if;
					
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