app.controller('fuentesInicioCtrl', ['$scope', 'comun', '$http', function($scope_, comun_, $http) {
        $scope_.modulo = 1;
        comun_.colocarSubtitulo("Fuentes de Noticias");
        $http.get(comun_.urlBackend + 'fuentes').success(function(respuesta) {
            $scope_.lista = respuesta.fuentes;
        });
            $http.get(comun_.urlBackend + "parametros/dominioAll", {params : {dominio: 'crawl'}}).success(function (res) {});
        $scope_.eliminar = function(id, $index) {
            var elimina = confirm("Esta seguro de eliminar esta fuente ??");
            if (elimina) {
                $http.delete(comun_.urlBackend + 'fuentes/' + id).success(function(resp) {
                    if (resp.mensaje) {
                        $scope_.lista.splice($index, 1);
                    }
                });

            }
        }
    }])
    .controller('fuentesNuevaCtrl', ['$scope', 'comun', '$http', function($scope, comun, $http) {
        $scope.modulo = 1;
        comun.colocarSubtitulo("Fuentes de Noticias");
        $scope.mensaje = {
            texto: '',
            estilo: $scope.estiloAlert[4],
            mostrar: false
        };
        //            $scope.mensaje = mensaje;

        function setNuevo() {
            $scope.contexto = {
                fuente_tipo: "RSS",
                prioridad: "2", // prioridad normal
                permite_rastrear: true
            };
        }

        setNuevo();

        angular.element("#formFuente input, #formFuente select ").keyup(function(event) {
            angular.element("#div_mensaje").hide(200);
        });

// $(function(){ 
//     $("txtFuenteNombre ").click(function(){
//         console.log("nninin")
//     })
// })
        $scope.guardar = function() {
            $scope.mensaje.mostrar = true;
            if ($scope.contexto.fuente_nombre === '' || $scope.contexto.fuente_seccion === '' || $scope.contexto.fuente_url === '' ||
                $scope.contexto.fuente_nombre === undefined || $scope.contexto.fuente_seccion === undefined || $scope.contexto.fuente_url === undefined) {
                mostrarMensaje($scope.mensaje, $scope.estiloAlert[1],
                    '<b>Los campos de Nombre, Seccion y URL no pueden estar vacios.</b>');
            } else {
                $http.post(comun.urlBackend + "fuentes", $scope.contexto).success(function(respuesta) {
                    if (respuesta.estado === "success") {
                        setNuevo();
                        mostrarMensaje($scope.mensaje, $scope.estiloAlert[4],
                            '<b>La fuente se guardo correctamente. Con el id : <a href="#/fuentes/editar/' + respuesta.fuente.id + '"  title="ver fuente" >\n\
                                                    ' + respuesta.fuente.id + '</a><b>\n\
                                                    <br><br> Para introducir una nueva fuente llene los campos nuevamente, para volver presione en <b>"volver al inicio"</b>');

                    } else {
                        mostrarMensaje($scope.mensaje, $scope.estiloAlert[3],
                            '<b>' + respuesta.mensaje + '<b> <a href="#/fuentes/editar/' + respuesta.fuente.id + '" class="badge bg-primary " title="ver fuente" >\n\
                                                    <i class="fa fa-search"></i><span> ver fuente</span></a>\n\
                                                    <br><br> Revise los campos del formulario, para volver presione en <b>"volver al inicio"</b>')
                    }
                });
            }
        };
    }])

    .controller('fuentesEditarCtrl', ['$scope', 'comun', '$routeParams', '$http', function($scope, comun, $routeParams, $http) {
        $scope.modulo = 1;
        comun.colocarSubtitulo("Fuentes de Noticias");
        $scope.contexto = {};
        $scope.mensaje = {
            texto: '',
            estilo: $scope.estiloAlert[4],
            mostrar: false,
        };

        var id = $routeParams.id;

        $http.get(comun.urlBackend + 'fuentes/' + id).success(function(res) {
            $scope.contexto = res.fuente;
            $scope.contexto.prioridad = $scope.contexto.prioridad.toString();
        });



        $scope.guardar = function() {
            $scope.mensaje.mostrar = true;
            if ($scope.contexto.fuente_nombre === '' || $scope.contexto.fuente_seccion === '' || $scope.contexto.fuente_url === '' ||
                $scope.contexto.fuente_nombre === undefined || $scope.contexto.fuente_seccion === undefined || $scope.contexto.fuente_url === undefined) {
                mostrarMensaje($scope.mensaje, $scope.estiloAlert[1],
                    '<b>Los campos de Nombre, Seccion y URL no pueden estar vacios.</b>');
            } else {
                $http.post(comun.urlBackend + 'fuentes', $scope.contexto).success(function(res) {
                    if (res.estado === 'success') {
                        comun.irA('/');
                    } else {
                        mostrarMensaje($scope.mensaje, $scope.estiloAlert[3],
                            '<b>' + res.mensaje + '<b> <a href="#/fuentes/editar/' + res.fuente.id + '" class="badge bg-primary " title="ver fuente" >\n\
                        <i class="fa fa-search"></i><span> ver fuente</span></a>\n\
                        <br><br> Revise los campos del formulario, para volver presione en <b>"volver al inicio"</b>')
                    }
                });
            }





        };
    }])


    .controller('configuracionCtrl', ['$scope', '$rootScope', 'comun', '$route', '$uibModal', '$http', function($scope, $rootScope, comun, $route, $uibModal, $http) {

        $scope.modulo = 3;
        comun.colocarSubtitulo("Configuración");
        $http.get(comun.urlBackend + 'configuracion/sw').success(function(respuesta) {
            $rootScope.lista = respuesta.stopwords;
        });
        $scope.muestraFormSW = function(id, $index) {
            if (!id) {
                $rootScope.contexto = {};
                $rootScope.contexto.activa = true;
                $rootScope.tituloModal = 'Agregar palabra "Stopword".';

            } else {
                $rootScope.tituloModal = 'Modificar palabra.';
                $http.get(comun.urlBackend + 'configuracion/sw/' + id).success(function(data) {
                    $rootScope.contexto = data.stopword;
                    $rootScope.index = $index;
                });
            }
            $rootScope.instanciaModal = $uibModal.open({
                animation: true,
                templateUrl: 'stopword-form_.html',
                controller: 'configuracionCtrl'
            })
        }

        $rootScope.guardar = function() {
            if (!$rootScope.contexto.id) {
                nuevaPalabra = {
                    id: $rootScope.contexto.id,
                    palabra: $rootScope.contexto.palabra,
                    categoria: $rootScope.contexto.categoria,
                    activa: $rootScope.contexto.activa
                };
                $http.post(comun.urlBackend + 'configuracion/sw', $rootScope.contexto).success(function(respuesta) {
                    $rootScope.lista.push(nuevaPalabra);
                });
            } else {
                stopword = $rootScope.contexto;
                $http.put(comun.urlBackend + 'configuracion/sw/' + $rootScope.contexto.id, $rootScope.contexto)
                    .success(function(respuesta) {

                        if (respuesta.mensaje) {
                            $rootScope.lista[$rootScope.index].id = stopword.id;
                            $rootScope.lista[$rootScope.index].palabra = stopword.palabra;
                            $rootScope.lista[$rootScope.index].categoria = stopword.categoria;
                            $rootScope.lista[$rootScope.index].activa = stopword.activa;
                        }
                    });
            }

            $rootScope.instanciaModal.close();
            $rootScope.contexto = {};
        }

        $rootScope.cancelar = function() {
            $rootScope.instanciaModal.dismiss('cancel');
            $rootScope.contexto = {};
        }

        $scope.eliminar = function(id) {
            var elimina = confirm("Esta seguro de eliminar esta palabra de la lista de stopwords (será borrada permanentemente) ??");
            if (elimina) {
                $http.delete(comun.urlBackend + 'configuracion/sw/' + id).success(function(resp) {
                    if (resp.mensaje) {
                        $route.reload();
                    }
                })
            }
        }
    }])

function sleep(sleepDuration) {
    var now = new Date().getTime();
    while (new Date().getTime() < now + sleepDuration) { /* do nothing */ }
}

function mostrarMensaje(mensaje, estilo, texto) {
    angular.element("#div_mensaje").hide(500);
    mensaje.texto = texto;
    mensaje.estilo = estilo;
    angular.element("#div_mensaje").show(500);
}

