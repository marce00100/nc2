
app.controller('fuentesInicioCtrl', ['$scope', 'comun', '$http', function($scope, comun, $http) {

    var ctx = {
        listaSecciones: [],
        fuentes:[],
        fuenteSeccion: {},
        mensaje: {
            texto: '',
            estilo: $scope.estiloAlert[4],
            mostrar: false
        },        

    }

    var fn = {
        cargarlista: function(){
            $http.get(comun.urlBackend + 'fuentes/listar').success(function(respuesta) {
                ctx.listaSecciones = respuesta.fuentes;

                var dataAdapter = new jqx.dataAdapter({
                    dataType: "json",
                    localdata: ctx.listaSecciones,
                    dataFields: [
                    { name: 'id_fuente', type: 'int' },
                    { name: 'fuente_nombre', type: 'string' },
                    { name: 'pais', type: 'string' },
                    { name: 'ciudad', type: 'string' },
                    { name: 'id_seccion', type: 'int' },
                    { name: 'seccion', type: 'string' },                
                    { name: 'url', type: 'string' },                
                    { name: 'tipo', type: 'string' },                
                    { name: 'permite_rastrear', type: 'string' },                
                    { name: 'prioridad', type: 'int' },                
                    { name: 'ultima_pasada', type: 'date' },                
                    { name: 'numero_pasadas', type: 'int' },                
                    { name: 'vigente', type: 'bool' },                          
                    ],
                });

                $("#dataTable").jqxDataTable({
                    source: dataAdapter,
                    altRows: true,
                    sortable: true,
                    width: "100%",
                    filterable: true,
                    columnsResize: true,
                    filterMode: 'simple',
                    selectionMode: 'singleRow',
                    // localization: getLocalization('es'),
                    columns: [
                        { text: 'Fuente nombre', dataField: 'fuente_nombre',},
                        { text: 'Sección', dataField: 'seccion',},
                        { text: 'Prioridad-Url', cellsRenderer: function(row, column, value,  rowData){
                            return `<span class="badge ${ rowData.prioridad == 1 ? 'bg-primary':'bg-info'} "> ${rowData.prioridad}</span>
                            <a href="${rowData.url}"><span> ${rowData.url}"</span></a>`;
                            } 
                        },
                        { text: 'Rastreo', dataField: 'permite_rastrear', width:35, cellsRenderer: function(row, column, value, rowData){
                            return `<span class="badge ${ value == 'N' ? 'bg-danger' : 'bg-success'}" >${value}</span>`;
                            }
                        },
                        { text: '-', width: 50, align:'center',  cellsalign: 'center', cellClassName: function(){ return 'bg-white darker'}, cellsRenderer: function(row, column, value, rowData){
                            return `<a href="" ng-click="fn.editar()" ><span class="fa fa-edit text-warning" title="editar"></span></a>
                            <a href="" ng-click="fn.eliminar('${rowData.id}', ${rowData.index})" ><span class="fa fa-minus-circle text-danger" title="eliminar"></span></a>`;
                            }
                        },
                        { text: 'Ult.Rastreo', dataField: 'ultima_pasada', width: 150, cellsformat: 'dd/MM/yyyy'},
                        { text: 'N rastreos', dataField: 'numero_pasadas', width: 60},
                        { text: 'Vig.', dataField: 'vigente', width: 40, cellsRenderer:function(row, value){ 
                            return `<strong class="${value ? 'text-success' : 'text-danger'} "> ${value ? 'S': 'N'} </strong>`;} 
                        }
                    ]
                });
            });

        },
        cargarFuentesForm:function(){
            $http.get(comun.urlBackend + 'fuentes/listarfuentes').success(function(respuesta) {
                ctx.fuentes = respuesta.data;
            });
        },
        cambiaFuenteForm: function(){
            var fuenteSel = ctx.fuentes.find(f =>  f.id == ctx.fuenteSeccion.id_fuente);
            ctx.fuenteSeccion.pais = fuenteSel.pais;
            ctx.fuenteSeccion.ciudad = fuenteSel.ciudad;
        },
        nuevo: function(){
            fn.setForm('nuevo');
            fn.showmodal();
        }, 
        editar: function(){
            fn.setForm('editar');
            fn.showmodal();
        },
        setForm: function(op){
            if(op=='nuevo'){
                ctx.fuente = {
                fuente_tipo: "RSS",
                prioridad: "2", // prioridad normal
                permite_rastrear: 'A'
                }
            }
            else if(op=='siguiente'){
                ctx.fuente = {
                    fuente_nombre : ctx.fuenteSeccion.fuente_nombre,
                    fuente_tipo: "RSS",
                    prioridad: "2", // prioridad normal
                    permite_rastrear: 'A'
                }
            }
            else if(op=='editar'){
                var rowSelected = $("#dataTable").jqxDataTable('getSelection');
                console.log(rowSelected)

            }
            // $http.get(comun.urlBackend + 'fuentes/' + id).success(function(res) {
            //     ctx.fuente = res.fuente;
            //     ctx.fuenteSeccion.prioridad = ctx.fuenteSeccion.prioridad.toString();
            // });
        },
        validateRules: function(){
            var reglasVal = {
                    errorClass: "state-error",
                    validClass: "state-success",
                    errorElement: "em",

                    rules: {
                        fuente_nombre: { required: true },
                        fuente_seccion:  { required: true },
                        fuente_url: { required: true }
                    },

                    messages:{
                        fuente_nombre: { required: 'Debe escribir un nombre de la fuente principal' },
                        fuente_seccion:  { required: 'Debe escribir una sección' },
                        fuente_url:  { required: 'La url no puede estar vacía' }
                    },

                    highlight: function(element, errorClass, validClass) {
                            $(element).closest('.field').addClass(errorClass).removeClass(validClass);
                    },
                    unhighlight: function(element, errorClass, validClass) {
                            $(element).closest('.field').removeClass(errorClass).addClass(validClass);
                    },
                    errorPlacement: function(error, element) {
                        if (element.is(":radio") || element.is(":checkbox")) {
                                element.closest('.option-group').after(error);
                        } else {
                                error.insertAfter(element.parent());
                        }
                    },
                    submitHandler: function(form) {
                        fn.saveData();
                    }
            }
            return reglasVal; 
        }, 
        saveData: function() {
            $http.post(comun.urlBackend + "fuentes", $scope.ctx.fuente).success(function(resp) {
                if (resp.estado == "success") {
                   fn.setForm('siguiente');
                   new PNotify({
                                title:'Guardado correctamente',
                                text: '',
                                shadow: true,
                                opacity: 0.9,
                                // addclass: noteStack,
                                type: "success",
                                // stack: Stacks[noteStack],
                                // width: findWidth(),
                                delay: 1500
                            });
                    $.magnificPopup.close(); 

                } else {
                    $("#mensaje").html(`<div class="bg-danger">${resp.mensaje}</div>` );
                }
            });
            
        },

        eliminar: function(id, $index) {
            var elimina = confirm("Esta seguro de eliminar esta fuente ??");
            if (elimina) {
                $http.delete(comun_.urlBackend + 'fuentes/' + id).success(function(resp) {
                    if (resp.mensaje) {
                       ctx.listaSecciones.splice($index, 1);
                    }
                });

            }
        },
        showmodal: function(){
            $(".state-error").removeClass("state-error")
            $("#form-plan em").remove();
                    // Inline Admin-Form example
            $.magnificPopup.open({
                removalDelay: 500, //delay removal by X to allow out-animation,
                focus: '#focus-blur-loop-select',
                items: {
                    src: "#fuente_seccion_modal"
                },
                // overflowY: 'hidden', //
                callbacks: {
                    beforeOpen: function(e) {
                        var Animation = "mfp-zoomIn";
                        this.st.mainClass = Animation;
                    }
                },
                midClick: true // allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
            });
        },
        cerrarModal: function(){
            $.magnificPopup.close();
        }

    }



    var listeners =  function (){

    }

    var init =  (function(){
        $scope.modulo = 1;
        $scope.template.titulo = "Fuentes de Noticias";

        $scope.ctx = ctx;
        $scope.fn = fn;

        $scope.fn.cargarlista();
        $scope.fn.cargarFuentesForm();
        $("#fuente_form").validate(fn.validateRules());
    })()


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




// function mostrarMensaje(mensaje, estilo, texto) {
//     angular.element("#div_mensaje").hide(500);
//     mensaje.texto = texto;
//     mensaje.estilo = estilo;
//     angular.element("#div_mensaje").show(500);
// }

