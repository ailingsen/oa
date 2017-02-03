//员工管理列表页
systemMod.controller('EmployeeAddCtrl',function($scope,$http,$rootScope,$timeout,employeeModel,$state,attendanceModel){
    var employee = $scope.employee = {};
    var employee_param = $scope.employee_param = {};
    var attend = $scope.attend = {};

    //是否有部门负责人
    employee.isSetManager = false;
    employee.manager_real_name = '';

    //参数
    employee_param.type = 'add';
    employee_param.real_name = '';
    employee_param.email = '';
    employee_param.position = '';
    employee_param.entry_time = '';
    employee_param.is_formal = '0';
    employee_param.org_id = '';
    employee_param.is_manager = 0;
    employee_param.card_no = '';
    employee_param.phone = '';
    employee_param.resumeId = '';

    //部门查询
    attend.search_org_name='';
    attend.search_org_name_temp='';

    //是否显示部门下拉列表
    attend.isOrgWin = false;
    //是否为保存并设置权限
    employee.isSavePer = false;

    //添加附件
    $scope.addFileBtn = function(uploader){
        uploader.url = '/index.php?r=management/employee/upload';
        uploader.onCompleteItem = function (fileItem, response, status, headers) {
            if(response.code!=1 && response.msg){
                alert(response.msg);
                return false;
            }else{
                employee_param.resumeId = response.data;
                uploader.clearQueue();
            }
        };
    };

    //获取所有搜索部门
    attend.getSearchAllOrgInfo = function(){
        attend.search_org_name='';
        employee_param.org_id = '';
        attendanceModel.getOrgInfo($scope,0);
    }

    //获取搜索部门数据
    attend.searchOrgInfo = function () {
        if(attend.search_org_name != attend.search_org_name_temp){
            attendanceModel.getOrgInfo($scope,0);
            attend.search_org_name_temp = attend.search_org_name;
        }
    }

    //保存选中的查询部门ID
    attend.selectOrg = function (obj) {
        employee_param.org_id = obj.value;
        attend.search_org_name = obj.label;
        employee_param.is_manager = 0;
        employeeModel.getOrgManagerInfo($scope);
        attend.isOrgWin = false;
    }

    //保存按钮
    employee.saveBtn = function(status){
        employee.isSavePer = status;
        employeeModel.addEmployee($scope);
    }

    //取消按钮
    employee.cancelBtn = function(){
        employeeModel.setInitMyCreateListCookie($scope);
        $state.go('^');
    }

    //简历下载
    employee.fileDownload = function(obj){
        window.location.href="/index.php?r=management/employee/downfile&filepath="+encodeURI(obj.file_path+obj.save_name)+'&file_name='+obj.file_name;
    }

});



