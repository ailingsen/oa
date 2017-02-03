MeetingMod.factory('ConferenceRoomModel',function($http,$state,$filter,$cookieStore){
    var  conferenceRoom={};
    var dateFilter = $filter('date');
    conferenceRoom.getConferenceRoomList = function($scope,searchTime) {
        $http.post('/index.php?r=boardroom/boardroom/room-reserve-list',{dataTime:searchTime})
            .success(function(data) {
                if(data.code==20000){
                    $scope.conferenceRoomList = data.data.list;
                    $scope.conferenceRoom.weekAfterTime = data.data.weekAfter;
                    angular.forEach(data.data.list, function(one, index){
                        $scope.conferenceRoom.reserveTimeArr[index] = [['9:00', '9:30', 0, {}],
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
                            ['17:30', '18:00', 0, {}]];
                        angular.forEach(one.reserves, function(two, index2) {
                            if(one.reserves[index2]['uid']== $scope.conferenceRoom.userInfoUid){
                                $scope.conferenceRoom.reserveTimeArr[index][two.time_type][2] = 2;
                            }else {
                                $scope.conferenceRoom.reserveTimeArr[index][two.time_type][2] = 1;
                            }
                            $scope.conferenceRoom.reserveTimeArr[index][two.time_type][3] = two;
                        });
                    });
                }else if (data.code==1){
                    alert(data.msg);
                }
            });
    };
    //会议室预定
    conferenceRoom.bookMeetingRoom = function($scope,types, dataTime,meetingName,bookTime, meeting, meetingDesc, memberInfo) {
        $http.post('/index.php?r=boardroom/boardroom/reserve',{types:types,dataTime:dataTime,meetingName:meetingName,bookTime:bookTime,meeting:meeting,meetingDesc:meetingDesc,memberInfo:memberInfo})
            .success(function(data) {
                if(data.code==20000){
                    alert(data.msg);
                    $scope.conferenceRoom.meetingName = '';
                    $scope.conferenceRoom.meetingDesc = '';
                    $scope.conferenceRoom.allMemberPageList.splice(0,$scope.conferenceRoom.allMemberPageList.length);
                    $scope.conferenceRoom.selectMemberSum = 0;
                    $('.reserve-msg').slideUp(function(){
                        var span=$('.packUp span');
                        var i=$('.packUp i');
                        span.html('编辑会议室详情');
                        i.html('&#xe609;');
                        $('.packUp').css('left','175px');
                    });
                    conferenceRoom.getConferenceRoomList($scope,dateFilter($scope.conferenceRoom.searchTime, 'yyyy-MM-dd'));
                    $scope.conferenceRoom.popupRoomCtr = false;
                    $('#masklayer1').hide();
                }else if(data.code==1){
                    alert(data.msg);
                }else if(data.code==2){
                    alert(data.msg);
                }
            });
    };
    //新增会议室
    conferenceRoom.addMeetingRoom = function($scope,name,floor,desc,hot) {
        $http.post('/index.php?r=boardroom/boardroom/add',{name:name,floor:floor,desc:desc,hot:hot})
            .success(function(data) {
                if(data.code==20000){
                    alert(data.msg);
                    $scope.meetIng.addShow = false;
                    $('#masklayer1').hide();
                    conferenceRoom.getAllMeetingRoom($scope);
                    $scope.meetIng.hot = '';
                    $scope.meetIng.name = '';
                    $scope.meetIng.dec = '';
                    $scope.meetIng.floor = '';
                }else if(data.code==-1){
                    alert(data.msg);
                }
            });
    };
    //查询所有会议室
    conferenceRoom.getAllMeetingRoom = function($scope) {
        $http.post('/index.php?r=boardroom/boardroom/select-all-meeting')
            .success(function(data) {
                $scope.meetingRoomList = data.data;
            });
    };
    //获取会议室相关信息
    conferenceRoom.getMeetingRoom = function($scope,meetingId) {
        $http.post('/index.php?r=boardroom/boardroom/get-meeting-info',{meetingId:meetingId})
            .success(function(data) {
                $scope.meetIng.editMeetingRoomList = data.data;
                $scope.meetIng.editHot = $scope.meetIng.editMeetingRoomList.hot;
                $scope.meetIng.editName = $scope.meetIng.editMeetingRoomList.name;
                $scope.meetIng.judgeNage = $scope.meetIng.editMeetingRoomList.name;
                $scope.meetIng.editDec = $scope.meetIng.editMeetingRoomList.desc;
                $scope.meetIng.editFloor = $scope.meetIng.editMeetingRoomList.floor;
            });
    };
    //编辑会议室
    conferenceRoom.editMeetingRoom = function($scope,meetingId, name, desc, floor, hot, flag) {
        $http.post('/index.php?r=boardroom/boardroom/edit-meeting-info',{meetingId:meetingId,name:name,desc:desc,floor:floor,hot:hot,flag:flag})
            .success(function(data) {
                if(data.code==-1){
                    alert(data.msg);
                }else {
                    alert('修改成功！');
                    $scope.meetIng.modifyShow = false;
                    $('#masklayer1').hide();
                    $scope.meetIng.flag = 1;
                    conferenceRoom.getAllMeetingRoom($scope);
                }
            });
    };
    //删除会议室
    conferenceRoom.deleteMeetingRoom = function($scope,meetingId) {
        $http.post('/index.php?r=boardroom/boardroom/delete-meeting',{meetingId:meetingId})
            .success(function(data) {
                $scope.meetIng.delMeetingPopupCtr = false;
                $('#masklayer1').hide();
                conferenceRoom.getAllMeetingRoom($scope);
            });
    };
    //取消我预定的会议室
    conferenceRoom.cancelReserve = function ($scope,resId) {
        $http.post('/index.php?r=boardroom/boardroom/cancel-reserve',{resId:resId})
            .success(function(data) {
                if(data.code==20000){
                    alert('取消成功！');
                    conferenceRoom.getConferenceRoomList($scope,dateFilter($scope.conferenceRoom.searchTime, 'yyyy-MM-dd'));
                    $('#masklayer1').hide();
                }

            });
    };
    //获取公司所有人
    conferenceRoom.getAllMembers = function($scope,realName) {
        $http.post('/index.php?r=workmate/workmate/get-all-members',{realName:realName})
            .success(function(data) {
                $scope.conferenceRoom.allMemberList = data.data;
            });
    };
    //预定会议室发送邮件
    conferenceRoom.sendEmail = function(meetingName,bookTime,meeting,meetingDesc,memberName) {
        $http.post('/index.php?r=boardroom/boardroom/send-email',{meetingName:meetingName,bookTime:bookTime,meeting:meeting,meetingDesc:meetingDesc,memberName:memberName})
            .success(function(data) {
            });
    };
    conferenceRoom.updateGalleryId = function (uid) {
        $http.post('/index.php?r=boardroom/boardroom/update-gallery-id',{uid:uid})
            .success(function(data) {
                var userInfo = $cookieStore.get('userInfo');
                userInfo.gallery = 2;
                $cookieStore.put('userInfo',userInfo);
            });
    };
    return conferenceRoom;
});
