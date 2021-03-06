<?php

namespace App\Repositories;

use App\Models\Student;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class StudentRepository implements StudentRepositoryInterface
{
    /**
     * 搜索学生(支持拼音和中文)
     */
    public function searchStudents($partOfStudentName, $departmentId = null, $limit = 10, $columns = ['*'])
    {
        //删除字符串中的空格
        $partOfStudentName = str_replace(' ', '', $partOfStudentName);

        //拆分中文和拼音
        $chinese = [];
        $pinyin = [];
        //匹配中文
        preg_match('/^[\x{4e00}-\x{9fa5}]+/u', $partOfStudentName, $chinese);
        //匹配拼音
        preg_match('/[a-zA-Z]+$/', $partOfStudentName, $pinyin);
        if (empty($chinese) && empty($pinyin)) {
            throw new InvalidParameterException('输入有误');
        }
        $chinese = isset($chinese[0]) ? $chinese[0] : '';
        $pinyins = isset($pinyin[0]) ? [$pinyin[0], $pinyin[0]] : [];

        $query = Student::query();
        if (!is_null($departmentId))
            $query->byDepartment($departmentId);

        //获取拼音处理类
        $pinyinObj = app('pinyin');
        if ('' !== $chinese) {
            $query->where('student_name', 'like', $chinese . '%');
            if (!empty($pinyin)) {
                $temp = $pinyinObj->name($chinese);

                $pinyins = [
                    implode('', array_map(function ($item) {
                        return $item[0];
                    }, $temp)) . $pinyins[0],
                    implode('', $temp) . $pinyins[1]
                ];
            }
        }
        if (!empty($pinyins)) {
            $query->where(function ($query) use ($pinyins) {
                $query->where('abbreviation_pinyin1', 'like', $pinyins[0] . '%')//是简拼?
                ->orWhere('abbreviation_pinyin2', 'like', $pinyins[0] . '%')//是简拼？
                ->orWhere('full_pinyin1', 'like', $pinyins[1] . '%')//是全拼？
                ->orWhere('full_pinyin2', 'like', $pinyins[1] . '%');//是全拼？
            });
        }
        return $query->orderBy('report_at')->limit($limit)->get($columns);
    }

    public function searchStudentsByStudentNum($studentNum, $departmentId = null, $limit = 10, $columns = ['*'])
    {
        $query = Student::query();
        if (!is_null($departmentId))
            $query->byDepartment($departmentId);
        if (strlen($studentNum) < 10) {
            $studentNum .= '%';
            $query->where('student_num', 'like', $studentNum);
        } else {
            $query->where('student_num', $studentNum);
        }
        return $query->orderBy('report_at')->limit($limit)->get($columns);

    }

    /**
     * 判断该学生姓名是否存在
     * @param $studentName
     */
    public function studentNameExist($studentName)
    {
        return Student::where('student_name', $studentName)->count() > 0;
    }

}
