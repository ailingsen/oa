
MeetingMod.controller('ReserveManage',function($scope,$http,$rootScope,Publicfactory,ConferenceRoomModel,$state){
    //��ӻ����ҵ��������ʾ������
    var meeting=$scope.meetIng={};
    meeting.addShow=false;
    meeting.hot = '';
    meeting.dec = '';
    meeting.name = '';
    meeting.floor = '';
    meeting.roomId = '';
    meeting.meetingRoomList = [];
    meeting.editMeetingRoomList = '';
    meeting.delMeetingPopupCtr = false;
    meeting.delMeetingName = '';
    meeting.delRoomId = '';
    meeting.judgeNage = '';
    meeting.flag = 1;
    
    meeting.addBtn=function(){
        meeting.addShow=true;
        $('#masklayer1').show();
    };
    //删除会议室
    meeting.delMeetingPopup = function (meetingName,roomId) {
        $('#masklayer1').show();
        meeting.delMeetingPopupCtr = true;
        meeting.delMeetingName = meetingName;
        meeting.delRoomId = roomId;
        $('#masklayer1').show();

    };
    meeting.cancelMeetingBtn = function () {
        $('#masklayer1').hide();
        meeting.delMeetingPopupCtr = false;
        $('#masklayer1').hide();

    };
    //�༭�����ҵ��������ʾ������
    meeting.modifyShow=false;
    meeting.modifyBtn=function(roomId){
        meeting.roomId = roomId;
        meeting.modifyShow=true;
        $('#masklayer1').show();
        ConferenceRoomModel.getMeetingRoom($scope,meeting.roomId);
    };
    //删除会议室
    meeting.delBtn = function(roomId) {
        meeting.roomId = roomId;
    };

    //ȡ����ť
    meeting.cancelBtn=function(){
        $scope.meetIng.hot = '';
        $scope.meetIng.name = '';
        $scope.meetIng.dec = '';
        $scope.meetIng.floor = '';
        meeting.addShow=false;
        meeting.modifyShow=false;
        $('#masklayer1').hide();
    };
    ConferenceRoomModel.getAllMeetingRoom($scope);
    //预定会议室
    meeting.editHot = '';
    meeting.editName = '';
    meeting.editDec = '';
    meeting.editFloor = '';

    meeting.reserveMeeting = function(){
        meeting.hot = $scope.meetIng.hot;
        meeting.name = $scope.meetIng.name;
        meeting.dec = $scope.meetIng.dec;
        meeting.floor = $scope.meetIng.floor;

        if(Publicfactory.checkEnCnstrlen(meeting.name)<=0){
            alert('请输入会议室名称！');
            return;
        }
        if(meeting.name.length>20){
            alert('会议室名称不能超过20个字！');
            return;
        }
        if(Publicfactory.checkEnCnstrlen(meeting.dec)<=0){
            alert('请输入会议室描述！');
            return;
        }
        if(Publicfactory.checkEnCnstrlen(meeting.floor)<=0){
            alert('请输入会议室楼层！');
            return;
        }
        if(Publicfactory.checkEnCnstrlen(meeting.hot)<=0){
            alert('请输入会议室排序！');
            return;
        }
        if(meeting.hot>=100 || meeting.hot<=0 || meeting.hot%1!=0){
            alert('会议室排序为正整数且不能大于99！');
            return;
        }
        ConferenceRoomModel.addMeetingRoom($scope,meeting.name,meeting.floor,meeting.dec,meeting.hot);
    };

    //编辑会议室
    meeting.editMeetingRoom = function() {
        meeting.editHot = $scope.meetIng.editHot;
        meeting.editName = $scope.meetIng.editName;
        meeting.editDec = $scope.meetIng.editDec;
        meeting.editFloor = $scope.meetIng.editFloor;
        if(Publicfactory.checkEnCnstrlen(meeting.editName)<=0){
            alert('请输入会议室名称！');
            return;
        }
        if(meeting.editName.length>20){
            alert('会议室名称不能超过20个字！');
            return;
        }
        if(Publicfactory.checkEnCnstrlen(meeting.editDec)<=0){
            alert('请输入会议室描述！');
            return;
        }
        if(Publicfactory.checkEnCnstrlen(meeting.editFloor)<=0){
            alert('请输入会议室楼层！');
            return;
        }
        if(Publicfactory.checkEnCnstrlen(meeting.editHot)<=0 ){
            alert('请输入会议室排序！');
            return;
        }

        if(meeting.editHot%1!=0 || meeting.editHot<0 ){
            alert('会议室排序为正整数且不能大于99！');
            return;
        }
        if(meeting.editHot%1!=0 || meeting.editHot>=100 ){
            alert('会议室排序为正整数且不能大于99！');
            return;
        }
        if(meeting.judgeNage != meeting.editName){
            console.log(111);
            meeting.flag = 2;
        }
        ConferenceRoomModel.editMeetingRoom($scope,meeting.roomId, meeting.editName, meeting.editDec, meeting.editFloor, meeting.editHot, meeting.flag);
    };

    //删除会议室
    meeting.delMeeting = function(roomId) {
        meeting.roomId = roomId;
        ConferenceRoomModel.deleteMeetingRoom($scope,meeting.roomId);
    }
});

