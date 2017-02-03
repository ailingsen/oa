var MeetingMod=angular.module('MeetingMod',[]);
MeetingMod.controller('Reserve',function($scope,$http,$rootScope,ConferenceRoomModel,$state,$timeout,$cookieStore,$filter,permissionService){
    if (!permissionService.checkPermission('BoadroomBoad')) {
        $state.go('main.index');
        return false;
    }
    var conference = $scope.conferenceRoom = {};
    var dateFilter = $filter('date');
    $scope.conferenceRoomList = [];
    conference.userInfoUid = $cookieStore.get('userInfo').u_id;
    conference.galleryId = $cookieStore.get('userInfo').gallery;
    conference.resId = '';
    conference.firstBookTime = '9:00';
    conference.lastBookTime = '9:30';
    conference.popupStatusCtr = false;
    conference.popupBookCtr = false;
    conference.popupRoomCtr = false;
    conference.isShowGuid=false;
    conference.beginTime = '';
    conference.weekAfterTime = '';
    conference.selectedType = [];
    conference.myDate = new Date();
    conference.cancelTimeElement = '';
    conference.searchTime = conference.myDate.valueOf();
    conference.curTime =  conference.myDate.getFullYear()+"-"+(conference.myDate.getMonth()+1)+"-"+conference.myDate.getDate();
    conference.bookTime = '';
    conference.popupStatusOneCtr = false;
    conference.meetingName = '';
    conference.meetingDesc = '';
    conference.memberDropCtr = false;
    conference.searchMemberName = '';
    conference.allMemberList = [];
    conference.bookRoom_name = '';
    conference.selectMemberSum = 0;
    conference.cancelBookTime = [];
    conference.allMemberPageList = [];
    conference.typeFlag = '';
    conference.roomArr = [];
    // 预订时间
    conference.reserveTime = [
        ['9:00', '9:30', 0, {}],
        ['9:30', '10:00', 0, {}],
        ['10:00', '10:30', 0, {}],
        ['10:30', '11:00', 0, {}],
        ['11:00', '11:30', 0, {}],
        ['11:30', '12:00', 0, {}],
        ['13:30', '14:00', 0, {}],
        ['14:00', '14:30', 0, {}],
        ['14:30', '15:00', 0, {}],
        ['15:00', '15:30', 0, {}],
        ['15:30', '16:00', 0, {}],
        ['16:00', '16:30', 0, {}],
        ['16:30', '17:00', 0, {}],
        ['17:00', '17:30', 0, {}],
        ['17:30', '18:00', 0, {}]
    ];
    conference.reserveTimeArr = [];
    ConferenceRoomModel.getConferenceRoomList($scope,conference.curTime);
    conference.getSelected = function() {
            conference.selectedType = [];
            conference.typeFlag = '';
            angular.element('.reserve-bar .time-select').find('li').each(function () {
                if ($(this).hasClass("reserve-green")) {
                    var tmp = {'timeId': $(this).attr('timeId'),'roomId':$(this).attr('roomId')};
                    conference.selectedType.push(tmp);
                }
            });
            var flag = conference.selectedType[0]['timeId'];
            var flagOne = conference.selectedType[conference.selectedType.length-1]['timeId'];
            if((flagOne-flag)!=(conference.selectedType.length-1)){
                conference.typeFlag = 1;
            }
        };
    conference.removeSelected = function() {
        conference.selectedType = [];
        angular.element('.reserve-bar .time-select').find('li').each(function () {
            if ($(this).hasClass("reserve-green") == false) {
            } else {
                $(this).removeClass("reserve-green").addClass("reserve-white");
            }
        });
    };
        //鼠标拖动选择功能
        $('div.meeting').mousedown(function(){
            //鼠标按下并移动时触发
            $("ul.time-select").delegate("li","mousemove",function(){
                if(!$(this).hasClass('reserve-blue')&&!$(this).hasClass('reserve-gray')){
                    $(this).removeClass('reserve-white').addClass('reserve-green');
                }

            })

            //鼠标离开时间选择条时触发
            $('div.reserve-bar').mouseleave(function(){
                console.log($(this));
                conference.getSelected();
                conference.firstBookTime = conference.reserveTime[conference.selectedType[0].timeId][0];
                conference.lastBookTime = conference.reserveTime[conference.selectedType[conference.selectedType.length-1].timeId][1];
                conference.bookTime = dateFilter(conference.searchTime, 'yyyy-MM-dd')+' '+conference.firstBookTime+'-' + conference.lastBookTime;
                conference.popupRoomCtr=true;
                conference.popupRoomCtr = true;
                $scope.$apply();
                $('#masklayer1').show();

            })
            //鼠标在时间选择条上松开时触发
            $('div.reserve-bar').mouseup(function(e){
                //只有选择上了才执行
                if($(this).find('li').hasClass('reserve-green')){
                    conference.getSelected();
                    conference.firstBookTime = conference.reserveTime[conference.selectedType[0].timeId][0];
                    conference.lastBookTime = conference.reserveTime[conference.selectedType[conference.selectedType.length-1].timeId][1];
                    conference.bookTime = dateFilter(conference.searchTime, 'yyyy-MM-dd')+' '+conference.firstBookTime+'-' + conference.lastBookTime;
                    conference.popupRoomCtr=true;
                    $scope.$apply();
                    $('#masklayer1').show();
                }
            });
        });
        //鼠标单击时间段时触发
        $("div.meeting-section").delegate('.reserve-bar li','click',function(){
            if(!$(this).hasClass('reserve-blue')&&!$(this).hasClass('reserve-gray')){
                $(this).removeClass('reserve-white').addClass('reserve-green');
                conference.getSelected();
                conference.firstBookTime = conference.reserveTime[conference.selectedType[0].timeId][0];
                conference.lastBookTime = conference.reserveTime[conference.selectedType[conference.selectedType.length-1].timeId][1];
                conference.bookTime = dateFilter(conference.searchTime, 'yyyy-MM-dd')+' '+conference.firstBookTime+'-' + conference.lastBookTime;
                conference.popupRoomCtr=true;
                $scope.$apply();
                $('#masklayer1').show();
            }
        })
        //解绑所有事件
        $(document).mouseup(function(){
            $('ul.time-select').undelegate();
            $('div.reserve-bar').unbind('mouseleave');
            $('div.reserve-bar').unbind('mouseup');
        });


        //预订会议室弹出框的收展与确认取消
        $('.packUp').click(function(){
            $('.reserve-msg').slideToggle(function(){
                var span=$('.packUp span');
                var i=$('.packUp i');
                if(span.html()=='收起'){
                   span.html('编辑会议室详情');
                    i.html('&#xe609;');
                    $('.packUp').css('left','175px');
                }else{
                    span.html('收起');
                    i.html('&#xe608;');
                    $('.packUp').css('left','200px')
                }
            });
        });
        conference.cancelBookRoom = function () {
            conference.meetingName = '';
            conference.meetingDesc = '';
            conference.allMemberPageList.splice(0,conference.allMemberPageList.length);
            conference.selectMemberSum = 0;
            conference.popupRoomCtr = false;
            conference.removeSelected();
            $('#masklayer1').hide();
            //将下拉框收起
            $('.reserve-msg').slideUp(function(){
                var span=$('.packUp span');
                var i=$('.packUp i');
                span.html('编辑会议室详情');
                i.html('&#xe609;');
                $('.packUp').css('left','175px');
            });
        };
        //预定会议室
        conference.sureReserve = function (event) {
            conference.beginTime = Date.parse(dateFilter(conference.searchTime, 'yyyy-MM-dd')+' '+conference.firstBookTime);
            if(conference.beginTime/1000>conference.weekAfterTime){
                alert('可预订时间为一周内！');
                return;
            }
            if(conference.typeFlag==1){
                alert('所选时间段不连续,请重新选择！');
                return;
            }
            if(conference.beginTime<((new Date()).valueOf())){
                alert('预定时间已过期！');
                return;
            }
            if(conference.meetingName.length>20){
                alert('会议室名称不能超过20个字！');
                return;
            }
            if(conference.meetingDesc.length>50){
                alert('会议室说明不能超过50个字！');
                return;
            }
            // if(conference.allMemberPageList.length>0){
            //     ConferenceRoomModel.sendEmail(conference.bookRoom_name,conference.bookTime,$scope.conferenceRoom.meetingName,$scope.conferenceRoom                .meetingDesc,conference.allMemberPageList)
            // }
            ConferenceRoomModel.bookMeetingRoom($scope,conference.selectedType, dateFilter(conference.searchTime, 'yyyy-MM-dd'),conference.bookRoom_name,conference.bookTime,$scope.conferenceRoom.meetingName,$scope.conferenceRoom.meetingDesc,conference.allMemberPageList);
            
        };
    //获取我预定的会议室resId
    conference.getReserveId = function(event,resId) {
        conference.resId = resId;
        conference.selectedType = [];
        var el = window.event || event;
            conference.cancelBookTime = [];
            angular.element(el.target).parents('.time-select').eq(0).find('li').each(function (index, one) {
                if (conference.resId && $(this).attr("reserve_id") == conference.resId && $(this).hasClass("reserve-blue")) {
                    $('#masklayer1').show();
                    var pem = conference.reserveTime[index];
                    conference.cancelBookTime.push(pem);
                    conference.firstBookTime = conference.cancelBookTime[0][0];
                    conference.lastBookTime = conference.cancelBookTime[conference.cancelBookTime.length-1][1];
                    conference.bookTime = dateFilter(conference.searchTime, 'yyyy-MM-dd') + ' ' + conference.firstBookTime + '-' + conference.lastBookTime;
                }
            });

        el = el.target;
        if(angular.element(el).parents('li').eq(0).hasClass('reserve-blue')){
            conference.cancelTimeElement = angular.element(el).parents('li').eq(0);
            conference.popupStatusCtr = !conference.popupStatusCtr;
        }

    };
    //取消我预定的会议室
    conference.cancelReserve = function(event) {
        conference.popupStatusCtr = !conference.popupStatusCtr;
        if(conference.cancelTimeElement != '' && conference.cancelTimeElement.hasClass('reserve-blue')){
            if(Date.parse(dateFilter(conference.searchTime, 'yyyy-MM-dd')+' '+conference.firstBookTime)<((new Date()).valueOf())){
                alert('时间已过期！');
                $('#masklayer1').hide();
                return;
            }
            ConferenceRoomModel.cancelReserve($scope,conference.resId);
        }
    };
    conference.cancelBookMt = function() {
        conference.popupStatusCtr = false;
        $('#masklayer1').hide();
    };
    conference.mouseActionUpCtr = function(event) {
        var el = window.event || event;
        el = el.target;
        if(angular.element(el).parents("li").eq(0).hasClass('reserve-gray') || angular.element(el).hasClass('reserve-gray')){
            angular.element(el).parents("li").eq(0).parents("ul").eq(0).find('.reserved-msg').hide();
            angular.element(el).parents("li").eq(0).find(".reserved-msg").show();
            angular.element(el).find(".reserved-msg").show();
        } else {
            angular.element(el).parents("li").eq(0).parents("ul").eq(0).find('.reserved-msg').hide();
        }
    };
    conference.mouseActionDownCtr = function(event,bookName) {
        var el = window.event || event;
        el = el.target;
        angular.element(el).find(".reserved-msg").hide();
        angular.element(el).parents("li").eq(0).find(".reserved-msg").hide();
        conference.bookRoom_name = bookName;
    };
    //时间搜索
    //$scope.conferenceRoom.searchstarttime=$filter('date')(conference.myDate,'yyyy-MM-dd');

    conference.searchTimeRoom = function () {
        ConferenceRoomModel.getConferenceRoomList($scope,dateFilter($scope.conferenceRoom.searchTime, 'yyyy-MM-dd'));
    };
    //填写预定会议室详细信息  选择人员下拉
    conference.memberListCtr = function () {
        conference.memberDropCtr = !conference.memberDropCtr;
        ConferenceRoomModel.getAllMembers($scope)
    };
    conference.selectMemberList = function(hearImg,name,uid) {
        conference.memberDropCtr = false;
        var data = {'headImg':hearImg, 'realName':name, 'uid':uid};
        conference.allMemberPageList.push(data);
        var result = [], isRepeated;
        var len = conference.allMemberPageList.length;
            for (var i = 0; i < len; i++) {
                isRepeated = false;
                var resultLen = result.length;
                for (var j = 0; j < resultLen; j++) {
                        if (conference.allMemberPageList[i].uid == result[j].uid) {
                                isRepeated = true;
                                 break;
                            }
                        }
                        if (!isRepeated) {
                             result.push(conference.allMemberPageList[i]);
                        }
                }
        conference.allMemberPageList = result;
        conference.selectMemberSum = conference.allMemberPageList.length;
    };
    //删除选择人员
    conference.delMember = function(index){
        conference.allMemberPageList.splice(index,1);
        conference.selectMemberSum = conference.allMemberPageList.length;
    };
    //清空所选人员
    conference.clearMember = function(){
        conference.allMemberPageList.splice(0,conference.allMemberPageList.length);
        conference.selectMemberSum = conference.allMemberPageList.length;
    };
    //
    conference.memMouseLeave = function () {
        conference.memberDropCtr = false;
        conference.searchMemberName = '';
    };
    //人员输入搜索
    conference.getInputMemberList = function() {
        ConferenceRoomModel.getAllMembers($scope,$scope.conferenceRoom.searchMemberName)
    };
    if(conference.galleryId==1){
        //鼠标滑动选择引导动画控制
        var section=angular.element('.meeting-section');
        section.one('mouseenter',function(){
            conference.isShowGuid=true;
            $scope.$apply();
            $('#masklayer1').show();
            var timer=setTimeout(function(){
                conference.isShowGuid=false;
                $scope.$apply();
                $('#masklayer1').hide();
                clearTimeout(timer);
            },4000)
        })
    }
    conference.hideGuid=function(){
        conference.isShowGuid=false;
        $('#masklayer1').hide();
        ConferenceRoomModel.updateGalleryId(conference.userInfoUid);
    }
});

























