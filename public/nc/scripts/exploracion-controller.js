app.controller('exploracionCtrl', ['$scope', '$http', 'comun', function ($scope, $http, comun)
{
	$scope.modulo = 4;
	$scope.ctx = {};
	$scope.filtros = {};
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
}])


