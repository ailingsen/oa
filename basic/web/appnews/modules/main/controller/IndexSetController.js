IndexMod.controller('indexSetCtr',function($scope,$state,$window,$http,deskModel){
    angular.element('#masklayer1').css('display','block');
    var index = $scope.index = {};
    var index_param = $scope.index_param = {};
    index_param.bigSelected = [];
    index_param.smallSelected = [];

    //默认模板
    index.bigTpl = [
        {'name' : 'mytask', 'is_select': false},
        {'name' : 'mywork', 'is_select': false},
        {'name' : 'attendance', 'is_select': false},
        {'name' : 'project', 'is_select': false},
        {'name' : 'notice' , 'is_select': false},
        {'name' : 'applyproval' , 'is_select': false},
        {'name' : 'scoreboard' , 'is_select': false},
        {'name' : 'survey' , 'is_select': false},
        {'name' : 'workstate' , 'is_select': false},
        {'name' : 'myapply' , 'is_select': false},
        {'name' : 'shortcut' , 'is_select': false},
    ];
    index.smallTpl = [
        {'name' : 's-workstate', 'is_select': false},
        {'name' : 's-apply', 'is_select': false},
        {'name' : 's-project', 'is_select': false},
        {'name' : 's-task', 'is_select': false},
        {'name' : 's-meeting', 'is_select': false},
        {'name' : 's-workmate', 'is_select': false},
    ]

    //获取首页模板
    deskModel.getDeskSet($scope);

    //设置弹出框取消按钮
    angular.element('.set-close').click(function(){
        $window.history.back();
        angular.element('#masklayer1').css('display','none');

    })

    //设置弹出框的选择事件
    angular.element('.set-mod').on( 'click','li',function(){
        if(!$(this).hasClass('set-active')) {
            if (angular.element('.set-mod').find('.set-active').length >= 6) {
                alert("只能选择6个功能模块");
                return;
            }
        }
        if($(this).hasClass('set-active') && 'shortcut' == $(this).attr('tpl-name')) {
            angular.element('.col-gray.f16').css('visibility','hidden');
            angular.element('.set-shortcut').css('visibility','hidden');
        }

        if(!$(this).hasClass('set-active') && 'shortcut' == $(this).attr('tpl-name')) {
            angular.element('.col-gray.f16').css('visibility','visible');
            angular.element('.set-shortcut').css('visibility','visible');
        }
        $(this).toggleClass('set-active');
    })
    angular.element('.set-shortcut').on('click','li',function(){
        if(!$(this).hasClass('shortcut-active')) {
            if (angular.element('.set-shortcut').find('.shortcut-active').length >= 4) {
                alert("只能选择4个快捷通道");
                return;
            }
        }
        $(this).toggleClass('shortcut-active');
    })

    //保存设置
    index.setIndex = function(){
        index_param.bigSelected = [];
        index_param.smallSelected = [];
        var selectShortCut = false;
        angular.element('.set-mod').find('li').each(function(index, item){

            if(angular.element(item).hasClass('set-active')) {
                index_param.bigSelected.push(angular.element(item).attr('tpl-name'));
                if(angular.element(item).attr('tpl-name').indexOf('shortcut') > -1){
                    selectShortCut = true;
                }
            }

        });
        // if (index_param.bigSelected.length < 5) {
        //     alert("必须选择5个功能模块");
        //     return;
        // }

        angular.element('.set-shortcut').find('li').each(function(index, item){
            if(angular.element(item).hasClass('shortcut-active')) {
                index_param.smallSelected.push(angular.element(item).attr('tpl-name'));
            }
        });
        if (selectShortCut && index_param.smallSelected.length < 4) {
            alert("必须选择4个快捷通道");
            return;
        }

        deskModel.editeDeskTpl($scope, $state);
    }
});