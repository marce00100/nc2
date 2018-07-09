// constantes y variables de config

var app = angular.module('appNews', ['ngRoute', 'ngSanitize', 'ui.bootstrap']);
app.config(['$routeProvider', function($routeProvider) {
        $routeProvider
            .when('/fuentes', {
                templateUrl: "templates/fuentes-lista.html",
                controller: "fuentesInicioCtrl"
            })
            .when('/fuentes/nueva', {
                templateUrl: "templates/fuentes-form.html",
                controller: "fuentesNuevaCtrl"
            })
            .when('/fuentes/editar/:id', {
                templateUrl: "templates/fuentes-form.html",
                controller: "fuentesEditarCtrl"
            })
            .when('/crawl', {
                templateUrl: "templates/crawl.html",
                controller: "crawlCtrl"
            })
            .when('/configuracion/sw', {
                templateUrl: "templates/stopwords-lista.html",
                controller: "configuracionCtrl"
            })
            .when('/exploracion', {
                templateUrl: "templates/bandeja-exploracion.html",
                controller: "bandejaExploracionCtrl"
            })
            // .when('/capturador', {
            //     templateUrl: "templates/capturador.html",
            //     controller: "capturadorCtrl"
            // })
            .otherwise({
                redirectTo: '/fuentes'
            });
    }])

    .factory('comun', function($location, $rootScope) {
        var fac = {};
        // fac.urlBackend = '/www/nc21/public/index.php/'; //'/newsCrawler-b/public/index.php/'; //
        fac.urlBackend = '/www/nc20/public/';
        fac.colocarSubtitulo = function(sub) {
            $rootScope.subtitulo = sub;
        };
        fac.menu = function(indice) {
            alert(indice);
            return indice;
        };
        fac.irA = function(ruta) {
            $location.url(ruta);
        };
        return fac;
    })
    .controller('AppCtrl', ['$rootScope', 'comun', '$scope',
        function AppCtrl($rootScope, comun, $scope) {
            $scope.estiloAlert = {
                '1': 'bg-danger-dark',
                '2': 'bg-warning',
                '3': 'bg-orange',
                '4': 'bg-success-dark'
            }

            $scope.cla = {
                'fuenteTipo': [
                    { 'id': 'RSS', 'nombre': 'RSS feed' }, 
                    { 'id': 'TWITTER', 'nombre': 'TWITTER'}
                ],
                'prioridad': [
                    { id: "2", nombre: "Normal"}, 
                    { id: "1",nombre: "Alta"}
                ],
                'permiteRastrear' : [
                    { 'id':'N',  'nombre':'No rastrear'}, 
                    { 'id':'A',  'nombre':'Automatica'}, 
                    { 'id':'M',  'nombre':'Manual'}, 
                ]
            }

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });
        }
    ]);