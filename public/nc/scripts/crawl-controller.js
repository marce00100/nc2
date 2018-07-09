app.controller('crawlCtrl', ['$scope', 'comun', '$http', function($scope, comun, $http) {



    // Contexto externo , variables del scope
    ctx = {
        running: 0,
        time_m: 0,
        t_ini: '',
        cicles: 0,
        error: 0,
        startCrawling: function() {
            fn.iniciarDetenerCrawl(1);
            angular.element("#btn_iniciar").toggleClass('bg-dark-light');
        },
        stopCrawling: function() {
            fn.iniciarDetenerCrawl(0);
            angular.element("#btn_iniciar").toggleClass('bg-dark-light');
        }
    };

    // FUNCION DE INICIO
    (function() {
        $scope = $.extend($scope, ctx);

        $.get(comun.urlBackend + "parametros/dominioAll", {dominio: 'crawl', codigo: 'running'}, function(res) {
            res.data.forEach(function(elem){
                ctx[elem.codigo] = elem.valor;
            })
            console.log(ctx);
        });
    })();







    // funciones
    fn = {
        iniciarDetenerCrawl: function(op) {
            ctx.running = op;
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
            
        }
    }





}])