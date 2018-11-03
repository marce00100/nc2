app.controller('crawlCtrl', ['$scope', '$rootScope', 'comun', '$http', function($scope, $rootScope, comun, $http) {


    comun.colocarSubtitulo('Realizar rastreo de noticias')
    // Contexto externo , variables del scope
    cx = {
        running: 0,
        time_m: 0,
        t_ini: '',
        cicles: 0,
        error: 0,
    }

    fn = {
       startCrawling: function() {
            // fn.iniciarDetenerCrawl(1);
            cx.running = 1;
            this.getNoticiasGrupo();

        },
        stopCrawling: function() {
            // fn.iniciarDetenerCrawl(0);
            cx.running = 0;
            clearTimeout($rootScope.timer);

        },
        iniciarDetenerCrawl: function(op) {
            cx.running = op;
            if(op == 1)
            {
                $http.get(comun.urlBackend + "crawler/iniciaparametros").success(function(res) 
                {
                    $http.get(comun.urlBackend + "crawler/run").success(function(res) {
                        console.log("terminadooooo el run  ");
                        console.log(res);
                    }); 
                });
            }
            else
            {
                $http.post(comun.urlBackend + "parametros/putValor", { dominio:'crawl', codigo: 'running', valor: 0}).success(function(res){
                    console.log('detenido....mediante botn...');
                })
            }            
        },
        getNoticiasGrupo: function(){  
            var i=0;     
            if($rootScope.timer) {}
            else{             
                $rootScope.timer = setInterval(function () {
                    console.log(++i);
                    $http.get(comun.urlBackend + "crawler/getNoticiasGrupo").success(function(res) {
                        angular.element("#div_noticias").slideUp('slow');
                        $nodosProc = 0;
                        fuenteResp = res.fuente;
                        nodosResp = res.data;
                        cantNodosFuente = nodosResp.length;

                        html = `<div class='row  bg-dark-light item-fuente'><h5><b> ${fuenteResp.fuente_nombre} - ${fuenteResp.fuente_seccion}
                        </b> .- Articulos nuevos: ${cantNodosFuente}
                        </h5></div>`;

                        for (j = 0; j < nodosResp.length; j++)
                        {
                            item = nodosResp[j];
                            html = html + `<div class='row item-nodo '>
                            <div class='col-md-10' id='item-${item.id}  '>
                            <a href='${item.link}'><b><span style='font-size:1.2em'>  ${item.titulo}  </span></b></a>
                            <p>  ${fuenteResp.fuente_nombre}   - (  ${fuenteResp.fuente_seccion}  ) - ${item.fecha_pub}  </p>
                            </div><div class='col-md-2 procesado' id='procesado-${item.id}  '>  <span class='badge bg-grey'><i class='fa fa-refresh fa-spin fa-lg fa-fw'></i><span>en proceso...</span></span> </div>
                            <div class=' col-md-12 '> -   ${item.descripcion}  </div></div>`;
                        }
                        angular.element("#div_noticias").html(html).show('slow');
                        $scope.numeroArticulos = $scope.numeroArticulos + res.articulos_encontrados;
                        $scope.nuevos = $scope.nuevos + res.articulos_encontrados;
                                //llena contenidos y normalizados 
                            });
                }, 5000);      
            }      
        },
    };

    // FUNCION DE INICIO
    (function() {
        $scope.cx  = cx;
        $scope.fn = fn;

        $.get(comun.urlBackend + "parametros/dominioAll", {dominio: 'crawl'}, function(res) {
            res.data.forEach(function(elem){
                cx[elem.codigo] = elem.valor;
            });
            console.log(cx);
        });
        $.get(comun.urlBackend + "crawler/conteo", function(r) {
        });
    })();







}])