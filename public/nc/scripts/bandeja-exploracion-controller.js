app.controller('bandejaExploracionCtrl', ['$scope', '$http', 'comun', function ($scope, $http, comun)
{
	$scope.modulo = 4;
	comun.colocarSubtitulo("Exploración de registros (noticias)");
	$scope.ctx = {};
	$scope.filtros = {};
	$scope.result = {};
	$scope.nodo = {};
	$scope.filtros.tiempo = 
					[
						{'id':'1', 'nombre':'hoy'},
						{'id':'2', 'nombre':'desde ayer'},
						{'id':'7', 'nombre':'ultima semana'},
						{'id':'14', 'nombre':'ultimas 2 semanas'},
					],

	$http.get(comun.urlBackend + 'explorar/obtenerfiltros').success(function(res){
		$scope.filtros.fuentes = res.fuentes;
		$scope.filtros.paises_ciudades = res.paises_ciudades;
	});

	$scope.limpiar = function(){
		$scope.ctx = {
			tiempo:'',
			id_fuente:'',
			fecha_desde:'',
			fecha_hasta:'',
			pais_ciudad:'',
			texto_busqueda:'',
			busqueda_profunda: false,
		};
		$scope.result = {
			lista: {},
			total_registros : null,
			pagina_actual: null,
			total_paginas: [],
			buscando : false
		};
	}

	$scope.cambiarTipoBusqueda = function()
	{
		angular.element("#divBusqueda").toggleClass('bg-default-dark');
		angular.element("#divBusqueda").toggleClass('bg-dark-light');
		if($scope.ctx.busqueda_profunda)
			angular.element("#lblBusqueda").html("Búsqueda profunda")		
		else
			angular.element("#lblBusqueda").html("Búsqueda normal")
	}

	$scope.buscar = function(pagina = 1){
		$scope.result.buscando = true;
		$scope.result.lista = [];
		$scope.result.total_paginas = [];
		$scope.ctx.pagina = pagina; //pagina actual
		$scope.ctx.limite = 20; // cantidad de registros que se van a obtener
		var paginas =[]

		$http.post(comun.urlBackend + 'explorar/obtenerbusqueda', $scope.ctx).success(function(res){
			for(i=1; i<= (res.total_registros/$scope.ctx.limite | 0 ) + 1 ; i++) //obtiene la parte entera
				paginas.push(i);
			$scope.result = {
				lista : res.data,
				total_registros : res.total_registros,
				pagina_actual : res.pagina,
				total_paginas : paginas, 
				buscando : false,
			}
			console.log($scope.result)
		});
	}

	$scope.verNodo = function(id_nodo)
	{
		$http.get(comun.urlBackend + 'explorar/obtenernoticia/' + id_nodo).success(function(res){
			$scope.nodo = res.data;
			angular.element("#modal_noticia").modal();
		})


	}



}])


