<?php
public function getTasksAllUsersAnd($post, $load, $calcCount = false, $and = null){
        if (isset($_COOKIE['current_filtergroup_id'])) {
            $filtergroup = \Entity\Filtergroup::find($_COOKIE['current_filtergroup_id']);
        }




            $this->db->select('multitask.id as id, multitask.date_period as period, multitask.full as multitask_full, multitask.status as status, multitask.priority, multitask.date_close, multitask.date_perform, multitask.date, multitask.date_begin');
            $this->db->select('multitask.date_period as date_period');
            $this->db->select('e1.name as employee_name, e1.surname as employee_surname, e1.middlename as employee_middle_name');
            $this->db->select('e3.name as kurator_name, e3.surname as kurator_surname, e3.middlename as kurator_middle_name, e3.id as kurator_id');
            $this->db->select('e2.surname as who_insert_surname, e2.name as who_insert_name, e2.middlename as who_insert_middlename, e2.id as who_insert_id');
            $this->db->select('favorite_work.work_id as is_elect, favorite_work.user_id as user_elect');
            $this->db->select('work.wrk_id as wrk_id');
            $this->db->select('wu.wus_viewed_work as work_viewed');
            $this->db->select('points.city as point_city, points.street as point_street, points.building as point_building');
            $this->db->select('points.name as adr_name, points.id as points_id');
            $this->db->select('wtchrs.wtchrs_ids as watcher');


            $this->db->select('department.id as dep_id');

            $this->db->from('multitask');
            $this->db->join('work', 'work.wrk_macrotaskid = multitask.id AND work.wrk_type = "task"', 'inner');


            $this->db->join('work_user as wu', 'work.wrk_id = wu.wus_wrk_id AND wu.wus_user_id = ' . USER_COOKIE_ID, 'left');
            $this->db->join('responsible_task', 'responsible_task.task_id = multitask.id', 'left');


            // Если присутствует метка inbox - Входящие,
            // делаем выборку только по user_id текущего пользователя
            if (isset($filtergroup->filter_attribute) && $filtergroup->filter_attribute == 'inbox') {
                $this->db->where('(responsible_task.user_id = ' . USER_COOKIE_ID . ' OR multitask.kurator = ' . USER_COOKIE_ID . ')');
            } else if (in_array('show_and_exec', $and) ){
                if (!empty($post['employee']) ) {
                    if (is_array($post['employee'])) {
                        foreach ($post['employee'] as $key => $employee) {
                            if ($key == 0) {
//                            $employees = ' AND responsible.user_id= '. $employee  ;
//                            $this->db->join('responsible_task AS responsible', 'responsible.task_id = multitask.id  '.$employees, 'inner');
                                $employees = '   AND responsibl_' . $key . '.user_id= ' . $employee;
                                $this->db->join('responsible_task AS responsibl_' . $key . '', 'responsibl_' . $key . '.task_id = multitask.id  ' . $employees, 'inner');
                            } else if ($key == 1) {
                                $employees1 = '   AND responsibl_' . $key . '.user_id= ' . $employee;
                                $this->db->join('responsible_task AS responsibl_' . $key . '', 'responsibl_' . $key . '.task_id = multitask.id  ' . $employees1, 'inner');
                            } else if ($key == 2) {
                                $employees2 = '   AND responsibl_' . $key . '.user_id= ' . $employee;
                                $this->db->join('responsible_task AS responsibl_' . $key . '', 'responsibl_' . $key . '.task_id = multitask.id  ' . $employees2, 'inner');
                            } else if ($key == 3) {
                                $employees3 = '   AND responsibl_' . $key . '.user_id= ' . $employee;
                                $this->db->join('responsible_task AS responsibl_' . $key . '', 'responsibl_' . $key . '.task_id = multitask.id  ' . $employees3, 'inner');
                            } else if ($key == 4) {
                                $employees4 = '   AND responsibl_' . $key . '.user_id= ' . $employee;
                                $this->db->join('responsible_task AS responsibl_' . $key . '', 'responsibl_' . $key . '.task_id = multitask.id  ' . $employees4, 'inner');
                            }
                        }

                    } else {
                        $this->db->where('responsible_task.user_id', $post['employee']);
                    }
                }
            }  else if (in_array('show_and_exe', $and) ){
                if (!empty($post['employee']) ) {
                    if (is_array($post['employee'])) {
                        $employees = '';
                        foreach ($post['employee'] as $key => $employee) {
                            if ($key == 0) {
                                $employees .= 'responsible_task.user_id= ' . $employee;
                            } else {
                                $employees .= ' OR responsible_task.user_id= ' . $employee;
                            }
                        }
                        $this->db->where('(' . $employees . ')');
                    } else {
                        $this->db->where('responsible_task.user_id', $post['employee']);
                    }
                }
            }


            $this->db->join('points', 'multitask.points_id = points.id', 'left');


            if (isset($filtergroup->filter_attribute) && $filtergroup->filter_attribute == 'watcher') {
                $this->db->join('(SELECT
        w1.wrk_macrotaskid as mtid,   
        GROUP_CONCAT(wu.wus_user_id SEPARATOR \',\') as wtchrs_ids
    FROM work w1         
    LEFT JOIN work_user wu ON
        w1.wrk_id = wu.wus_wrk_id and wu.wus_user_id = ' . USER_COOKIE_ID . '
        inner JOIN work_user_watcher wuw on wuw.wuw_wus_id = wu.wus_id
        where w1.wrk_type=\'task\'
    group by w1.wrk_id) as wtchrs', 'multitask.id = wtchrs.mtid', 'inner');
            } elseif (!empty($post['watcher']) && in_array('show_and_watch', $and) ) {

                    if (is_array($post['watcher'])) {
                        $watchers = '';
                        foreach ($post['watcher'] as $key => $watcher) {
                            if ($key == 0) {
                                $watchers .= 'wu.wus_user_id = ' . $watcher;

                                $this->db->join('(SELECT
        w1.wrk_macrotaskid as mtid,   
        GROUP_CONCAT(wu.wus_user_id SEPARATOR \',\') as wtchrs_ids
    FROM work w1         
    LEFT JOIN work_user wu ON
        w1.wrk_id = wu.wus_wrk_id and ' . $watchers . '
        inner JOIN work_user_watcher wuw on wuw.wuw_wus_id = wu.wus_id
        where w1.wrk_type=\'task\'
    group by w1.wrk_id) as wtchrs', 'multitask.id = wtchrs.mtid', 'inner');
                            } else if ($key == 1) {
                                $watchers1 = '  wu1.wus_user_id= ' . $watcher;
                                $this->db->join('(SELECT
        w11.wrk_macrotaskid as mtid1,   
        GROUP_CONCAT(wu1.wus_user_id SEPARATOR \',\') as wtchrs_ids1
    FROM work w11        
    LEFT JOIN work_user wu1 ON
        w11.wrk_id = wu1.wus_wrk_id and ' . $watchers1 . '
        inner JOIN work_user_watcher wuw1 on wuw1.wuw_wus_id = wu1.wus_id
        where w11.wrk_type=\'task\'
    group by w11.wrk_id) as wtchrs1', 'multitask.id = wtchrs1.mtid1', 'inner');
                            } else if ($key == 2) {
                                $watchers2 = '  wu2.wus_user_id= ' . $watcher;
                                $this->db->join('(SELECT
        w2.wrk_macrotaskid as mtid2,   
        GROUP_CONCAT(wu2.wus_user_id SEPARATOR \',\') as wtchrs_ids2
    FROM work w2        
    LEFT JOIN work_user wu2 ON
        w2.wrk_id = wu2.wus_wrk_id and ' . $watchers2 . '
        inner JOIN work_user_watcher wuw2 on wuw2.wuw_wus_id = wu2.wus_id
        where w2.wrk_type=\'task\'
    group by w2.wrk_id) as wtchrs2', 'multitask.id = wtchrs2.mtid2', 'inner');
                            } else if ($key == 3) {
                                $watchers3 = '  wu3.wus_user_id= ' . $watcher;
                                $this->db->join('(SELECT
        w3.wrk_macrotaskid as mtid3,   
        GROUP_CONCAT(wu3.wus_user_id SEPARATOR \',\') as wtchrs_ids3
    FROM work w3       
    LEFT JOIN work_user wu3 ON
        w3.wrk_id = wu3.wus_wrk_id and ' . $watchers3 . '
        inner JOIN work_user_watcher wuw3 on wuw3.wuw_wus_id = wu3.wus_id
        where w3.wrk_type=\'task\'
    group by w3.wrk_id) as wtchrs3', 'multitask.id = wtchrs3.mtid3', 'inner');
                            } else if ($key == 4) {
                                $watchers4 = '  wu4.wus_user_id= ' . $watcher;
                                $this->db->join('(SELECT
        w4.wrk_macrotaskid as mtid4,   
        GROUP_CONCAT(wu4.wus_user_id SEPARATOR \',\') as wtchrs_ids4
    FROM work w4       
    LEFT JOIN work_user wu4 ON
        w4.wrk_id = wu4.wus_wrk_id and ' . $watchers4 . '
        inner JOIN work_user_watcher wuw4 on wuw4.wuw_wus_id = wu4.wus_id
        where w4.wrk_type=\'task\'
    group by w4.wrk_id) as wtchrs4', 'multitask.id = wtchrs4.mtid4', 'inner');
                            }
                        }

                    } else {
                        $this->db->join('(SELECT
        w1.wrk_macrotaskid as mtid,   
        GROUP_CONCAT(wu.wus_user_id SEPARATOR \',\') as wtchrs_ids
    FROM work w1         
    LEFT JOIN work_user wu ON
        w1.wrk_id = wu.wus_wrk_id and wu.wus_user_id = ' . $post['watcher'] . '
        inner JOIN work_user_watcher wuw on wuw.wuw_wus_id = wu.wus_id
        where w1.wrk_type=\'task\'
    group by w1.wrk_id) as wtchrs', 'multitask.id = wtchrs.mtid', 'inner');
                    }
            }   elseif ( !empty($post['watcher']) && in_array('show_and_watc', $and) ) {
                    if (is_array($post['watcher'])) {
                        $watchers = '';
                        foreach ($post['watcher'] as $key => $watcher) {
                            if ($key == 0) {
                                $watchers .= 'wu.wus_user_id = ' . $watcher;
                            } else {
                                $watchers .= ' OR w1.wrk_id = wu.wus_wrk_id and  wu.wus_user_id= ' . $watcher;
                            }
                        }
                        $this->db->join('(SELECT
        w1.wrk_macrotaskid as mtid,   
        GROUP_CONCAT(wu.wus_user_id SEPARATOR \',\') as wtchrs_ids
    FROM work w1         
    LEFT JOIN work_user wu ON
        w1.wrk_id = wu.wus_wrk_id and ' . $watchers . '
        inner JOIN work_user_watcher wuw on wuw.wuw_wus_id = wu.wus_id
        where w1.wrk_type=\'task\'
    group by w1.wrk_id) as wtchrs', 'multitask.id = wtchrs.mtid', 'inner');
                    } else {
                        $this->db->join('(SELECT
        w1.wrk_macrotaskid as mtid,   
        GROUP_CONCAT(wu.wus_user_id SEPARATOR \',\') as wtchrs_ids
    FROM work w1         
    LEFT JOIN work_user wu ON
        w1.wrk_id = wu.wus_wrk_id and wu.wus_user_id = ' . $post['watcher'] . '
        inner JOIN work_user_watcher wuw on wuw.wuw_wus_id = wu.wus_id
        where w1.wrk_type=\'task\'
    group by w1.wrk_id) as wtchrs', 'multitask.id = wtchrs.mtid', 'inner');
                    }

            }else {
                $this->db->join('(SELECT
        w1.wrk_macrotaskid as mtid,   
        GROUP_CONCAT(wus_user_id SEPARATOR \',\') as wtchrs_ids
    FROM work w1         
    LEFT JOIN work_user wu ON
        w1.wrk_id = wu.wus_wrk_id
        inner JOIN work_user_watcher wuw on wuw.wuw_wus_id = wu.wus_id
        where w1.wrk_type=\'task\'
    group by w1.wrk_id) as wtchrs', 'multitask.id = wtchrs.mtid', 'left');
            }

            if (!empty($post['brand'])) {
                $this->db->join('brand', 'points.brand_id = brand.id', 'left');
                $this->db->select('brand.id as brand_id');
            }


            $this->db->join('users as us2', 'multitask.iniciator = us2.id', 'left');

            $this->db->join('users as us3', 'multitask.kurator = us3.id', 'left');
            $this->db->join('employee as e3', 'e3.id = us3.employee_id', 'left');


            $this->db->join('employee as e2', 'us2.employee_id = e2.id', 'inner');

            if (isset($filtergroup->filter_attribute) && $filtergroup->filter_attribute == 'favorites') {
                $this->db->join('favorite_work', 'work.wrk_id = favorite_work.work_id AND favorite_work.user_id = ' . USER_COOKIE_ID, 'inner');
            } else {
                $this->db->join('favorite_work', 'work.wrk_id = favorite_work.work_id AND favorite_work.user_id = ' . USER_COOKIE_ID, 'left');
            }


//        $this->db->join('users as us1', 'responsible_task.user_id = us1.id', 'inner');
//        $this->db->join('employee as e1', 'us1.employee_id = e1.id', 'inner');


            $this->db->join('users as us4', 'responsible_task.user_id = us4.id', 'inner');
            $this->db->join('employee as e1', 'us4.employee_id = e1.id', 'inner');
            $this->db->join('department', 'e1.dep_id = department.dep_id', 'left');


//        if (!empty($post['employee'])) {
//            $this->db->join('users', 'responsible.user_id = users.id ' , 'inner');
//            $this->db->join('employee as e1', 'users.employee_id = e1.id', 'inner');
//            $this->db->join('department', 'e1.dep_id = department.dep_id', 'left');
//        }else{
//
//            $this->db->join('users as us4', 'responsible_task.user_id = us4.id' , 'inner');
//            $this->db->join('employee as e1', 'us4.employee_id = e1.id', 'inner');
//            $this->db->join('department', 'e1.dep_id = department.dep_id', 'left');
//        }


            if (!empty($post['komentator'])) {
//            $this->db->join('comments_task', 'multitask.id = comments_task.task_id', 'left');
            }


            if (!empty($post['department']) && in_array('show_and_dep', $and) ) {

                if (is_array($post['department'])) {
                    $departments = '';
                    foreach ($post['department'] as $key => $department) {
                        if ($key == 0) {
                            $departments .= 'department.id= ' . $department;
                        } else {
                            $departments .= ' AND department.id= ' . $department;
                        }
                    }
                    $this->db->where('(' . $departments . ')');
                } else {
                    $this->db->where('department.id', $post['department']);
                }
            }elseif ( in_array('show_and_de', $and) ) {
                if (!empty($post['department'])) {
                    if (is_array($post['department'])) {
                        $departments = '';
                        foreach ($post['department'] as $key => $department) {
                            if ($key == 0) {
                                $departments .= 'department.id= ' . $department;
                            } else {
                                $departments .= ' OR department.id= ' . $department;
                            }
                        }
                        $this->db->where('(' . $departments . ')');
                    } else {
                        $this->db->where('department.id', $post['department']);
                    }
                }

            }


            // Если присутствует метка outbox - Исходящие,
            // делаем выборку только по user_id текущего пользователя
            if (isset($filtergroup->filter_attribute) && $filtergroup->filter_attribute == 'outbox') {
                $this->db->where('(responsible_task.who_insert_id= ' . USER_COOKIE_ID . ' OR multitask.iniciator = ' . USER_COOKIE_ID . ')');
            } else {
                if (!empty($post['executor']) && in_array('show_and_inic', $and) ) {
                    if (is_array($post['executor'])) {
                        $executors = '';
                        foreach ($post['executor'] as $key => $executor) {
                            if ($key == 0) {
                                $executors .= 'multitask.iniciator= ' . $executor;
                            } else {
                                $executors .= ' AND multitask.iniciator= ' . $executor;
                            }
                        }
                        $this->db->where('(' . $executors . ')');
                    } else {
                        $this->db->where('multitask.iniciator', $post['executor']);
                    }
                } elseif (!empty($post['executor']) && in_array('show_and_ini', $and) ) {
                    if (is_array($post['executor'])) {
                        $executors = '';
                        foreach ($post['executor'] as $key => $executor) {
                            if ($key == 0) {
                                $executors .= 'multitask.iniciator= ' . $executor;
                            } else {
                                $executors .= ' OR multitask.iniciator= ' . $executor;
                            }
                        }
                        $this->db->where('(' . $executors . ')');
                    } else {
                        $this->db->where('multitask.iniciator', $post['executor']);
                    }
                }
            }

            if (!empty($post['status']) && in_array('show_and_status', $and)) {
                if (is_array($post['status'])) {
                    $statuss = '';
                    foreach ($post['status'] as $key => $status) {
                        if ($key == 0) {
                            $statuss .= 'multitask.status= ' . $status;
                        } else {
                            $statuss .= ' AND multitask.status= ' . $status;
                        }
                    }
                    $this->db->where('(' . $statuss . ')');
                } else {
                    $this->db->where('multitask.status', $post['status']);
                }
            }if (!empty($post['status']) && in_array('show_and_statu', $and) ) {
                if (is_array($post['status'])) {
                    $statuss = '';
                    foreach ($post['status'] as $key => $status) {
                        if ($key == 0) {
                            $statuss .= 'multitask.status= ' . $status;
                        } else {
                            $statuss .= ' OR multitask.status= ' . $status;
                        }
                    }
                    $this->db->where('(' . $statuss . ')');
                } else {
                    $this->db->where('multitask.status', $post['status']);
                }
            }

            if (!empty($post['komentator']) && in_array('show_and_koment', $and) ) {
                $this->db->join('room', 'room.work_id = work.wrk_id', 'inner');
                if (is_array($post['komentator'])) {
                    foreach ($post['komentator'] as $key => $komentator) {
                        if ($key == 0) {
                            $komentators = ' AND rm_' . $key . '.user_from= ' . $komentator;
                            $this->db->join('room_messages AS  rm_' . $key . '', 'room.id = rm_' . $key . '.room_id ' . $komentators, 'inner');
                        } else if ($key == 1) {
                            $komentator1 = ' AND rm_' . $key . '.user_from= ' . $komentator;
                            $this->db->join('room_messages AS  rm_' . $key . '', 'room.id = rm_' . $key . '.room_id ' . $komentator1, 'inner');
                        } else if ($key == 2) {
                            $komentator2 = ' AND rm_' . $key . '.user_from= ' . $komentator;
                            $this->db->join('room_messages AS  rm_' . $key . '', 'room.id = rm_' . $key . '.room_id ' . $komentator2, 'inner');
                        } else if ($key == 3) {
                            $komentator3 = ' AND rm_' . $key . '.user_from= ' . $komentator;
                            $this->db->join('room_messages AS  rm_' . $key . '', 'room.id = rm_' . $key . '.room_id ' . $komentator3, 'inner');
                        } else if ($key == 4) {
                            $komentator4 = ' AND rm_' . $key . '.user_from= ' . $komentator;
                            $this->db->join('room_messages AS  rm_' . $key . '', 'room.id = rm_' . $key . '.room_id ' . $komentator4, 'inner');
                        }
                    }
                } else {
                    $this->db->where('comments_task.user_id', $post['komentator']);
                }
            }else if (!empty($post['komentator']) && in_array('show_and_komen', $and) ) {

//                $this->db->join('comments_task', 'multitask.id = comments_task.task_id', 'left');
                $this->db->join('room', 'room.work_id = work.wrk_id', 'inner');

                if (is_array($post['komentator'])) {
                    $komentators = '';
                    foreach ($post['komentator'] as $key => $komentator) {
                        if ($key == 0) {
                            $komentators .= ' AND room_messages.user_from= ' . $komentator;
                        } else {
                            $komentators .= ' OR room.id = room_messages.room_id AND room_messages.user_from= ' . $komentator;
                        }
                    }
                    $this->db->join('room_messages', 'room.id = room_messages.room_id ' . $komentators, 'inner');
//                $this->db->where('(' .$komentators . ')');
                } else {
                    $this->db->where('comments_task.user_id', $post['komentator']);
                }
            }
//
            if (!empty($post['kurator']) && in_array('show_and_kur', $and) ) {
                if (is_array($post['kurator'])) {
                    $kurators = '';
                    foreach ($post['kurator'] as $key => $kurator) {
                        if ($key == 0) {
                            $kurators .= 'multitask.kurator= ' . $kurator;
                        } else {
                            $kurators .= ' AND multitask.kurator= ' . $kurator;
                        }
                    }
                    $this->db->where('(' . $kurators . ')');
                } else {
                    $this->db->where('multitask.kurator', $post['kurator']);
                }
            } else if (!empty($post['kurator']) && in_array('show_and_ku', $and) ) {
                if (is_array($post['kurator'])) {
                    $kurators = '';
                    foreach ($post['kurator'] as $key => $kurator) {
                        if ($key == 0) {
                            $kurators .= 'multitask.kurator= ' . $kurator;
                        } else {
                            $kurators .= ' OR multitask.kurator= ' . $kurator;
                        }
                    }
                    $this->db->where('(' . $kurators . ')');
                } else {
                    $this->db->where('multitask.kurator', $post['kurator']);
                }
            }

            if (!empty($post['object']) && in_array('show_and_object', $and) ) {
                if (is_array($post['object'])) {
                    $objects = '';
                    foreach ($post['object'] as $key => $object) {
                        if ($key == 0) {
                            $objects0 = 'multitask.points_id= ' . $object;
                            $this->db->where('(' . $objects0 . ')');
                        } else if ($key == 1) {
                            $objects1 = 'multitask.points_id= ' . $object;
                            $this->db->where('(' . $objects1 . ')');
                        } else if ($key == 2) {
                            $objects2 = 'multitask.points_id= ' . $object;
                            $this->db->where('(' . $objects2 . ')');
                        } else if ($key == 3) {
                            $objects3 = 'multitask.points_id= ' . $object;
                            $this->db->where('(' . $objects3 . ')');
                        } else if ($key == 4) {
                            $objects4 = 'multitask.points_id= ' . $object;
                            $this->db->where('(' . $objects4 . ')');
                        }
                    }
                } else {
                    $this->db->where('multitask.points_id', $post['object']);
                }
            } elseif (!empty($post['object']) && in_array('show_and_objec', $and)) {
                if (is_array($post['object'])) {
                    $objects = '';
                    foreach ($post['object'] as $key => $object) {
                        if ($key == 0) {
                            $objects .= 'multitask.points_id= ' . $object;
                        } else {
                            $objects .= ' OR multitask.points_id= ' . $object;
                        }
                    }
                    $this->db->where('(' . $objects . ')');
                } else {
                    $this->db->where('multitask.points_id', $post['object']);
                }
            }


            if (!empty($post['brand']) && in_array('show_and_brand', $and) ) {
                if (is_array($post['brand'])) {
                    $brands = '';
                    foreach ($post['brand'] as $key => $brand) {
                        if ($key == 0) {
                            $brands .= 'brand.id= ' . $brand;
                        } else {
                            $brands .= ' AND brand.id= ' . $brand;
                        }
                    }
                    $this->db->where('(' . $brands . ')');
                } else {
                    $this->db->where('brand.id', $post['brand']);
                }
            } elseif (!empty($post['brand']) && in_array('show_and_bran', $and) ) {
                if (is_array($post['brand'])) {
                    $brands = '';
                    foreach ($post['brand'] as $key => $brand) {
                        if ($key == 0) {
                            $brands .= 'brand.id= ' . $brand;
                        } else {
                            $brands .= ' OR brand.id= ' . $brand;
                        }
                    }
                    $this->db->where('(' . $brands . ')');
                } else {
                    $this->db->where('brand.id', $post['brand']);
                }
            }


            if (!empty($post['priority']) && in_array('show_and_priority', $and)  ) {
                if (is_array($post['priority'])) {
                    $priorits = '';
                    foreach ($post['priority'] as $key => $priority) {
                        if ($key == 0) {
                            $priorits .= 'multitask.priority= ' . $priority;
                        } else {
                            $priorits .= ' AND multitask.priority= ' . $priority;
                        }
                    }
                    $this->db->where('(' . $priorits . ')');
                } else {
                    $this->db->where('multitask.priority', $post['priority']);
                }
            }elseif (!empty($post['priority']) && in_array('show_and_priorit', $and)  ) {
                if (is_array($post['priority'])) {
                    $priorits = '';
                    foreach ($post['priority'] as $key => $priority) {
                        if ($key == 0) {
                            $priorits .= 'multitask.priority= ' . $priority;
                        } else {
                            $priorits .= ' OR multitask.priority= ' . $priority;
                        }
                    }
                    $this->db->where('(' . $priorits . ')');
                } else {
                    $this->db->where('multitask.priority', $post['priority']);
                }
            }

            if (isset($post['from']) && !empty($post['from']) && $post['from'] != '1970-01-01') {
                $this->db->where('multitask.date >', $post['from']);
            }
            if (isset($post['to']) && !empty($post['to']) && $post['to'] != '1970-01-01') {
                $this->db->where('multitask.date <', $post['to']);
            }


            if (isset($post['expired']) && $post['expired'] == 1) {
                $this->db->where('(
            (multitask.status = 1 AND multitask.date_period  < "' . date('Y-m-d H:i:s', time()) . '") 
            OR (multitask.status = 2 AND multitask.date_perform > multitask.date_period)
            OR (multitask.status = 3 AND multitask.date_close > multitask.date_period AND (multitask.date_perform > multitask.date_period OR multitask.date_perform IS NULL))
            OR (multitask.status = 4 AND multitask.date_close_director > multitask.date_period AND (multitask.date_perform > multitask.date_period OR multitask.date_perform IS NULL AND multitask.date_close > multitask.date_period))
            )');
            } elseif (isset($post['expired']) && $post['expired'] == 2) {
                $this->db->where('(
            (multitask.status = 1 AND multitask.date_period  > "' . date('Y-m-d H:i:s', time()) . '") 
            OR (multitask.status = 2 AND multitask.date_perform < multitask.date_period)
            OR (multitask.status = 3 AND multitask.date_close < multitask.date_period AND (multitask.date_perform < multitask.date_period OR multitask.date_perform IS NULL))
            OR (multitask.status = 4 AND multitask.date_close_director < multitask.date_period AND (multitask.date_perform < multitask.date_period OR multitask.date_perform IS NULL AND multitask.date_close < multitask.date_period))
            )');
            }


            if (!empty($post['search'])) {
                if (!empty($post['komentator'])) {
                    $searchInComment = " OR comments_task.text LIKE '%" . $post['search'] . "%'";
                } else {
                    $searchInComment = '';
                }

                $this->db->join('checklist_work', 'checklist_work.work_id = work.wrk_id', 'left');
                $this->db->join('checklists', 'checklists.id = checklist_work.checklist_id', 'left');
                $this->db->join('checklist_elements', 'checklist_elements.checklist_id = checklists.id', 'left');

                $like_s = "(multitask.id LIKE '%" . $post['search'] . "%' OR multitask.full LIKE '%" . $post['search'] . "%' OR checklists.name LIKE '%" . $post['search'] . "%' OR checklist_elements.name LIKE '%" . $post['search'] . "%'" . $searchInComment . ")";
                $this->db->where($like_s);

            }
            if (empty($post['department']) && empty($post['employee']) && empty($post['status']) && empty($post['executor']) && empty($post['object']) && empty($post['brand']) && empty($post['priority'])) {
                //$this->db->where('multitask.status !=', 3);
                //$this->db->where('multitask.status !=', 4);
            }

            $this->db->where('multitask.trash', 0);

            // Если не директор
            // Только задачи текущего пользователя
            // доступ ко всем задачам всех сотрудников регулируется правами
            // используемыми в прошлом для доступа к странице "Все задачи".

            $success = 0;

            $query_permissions = $this->db->query("SELECT permission_id, status FROM permissions_users WHERE user_id = " . USER_COOKIE_ID);
            $permissions = $query_permissions->result();

            foreach ($permissions as $permission) {
                if ($permission->permission_id == 8 && $permission->status == 1) {
                    $success = 1;
                    break;
                }
            }

            // Если правами  не регулируется доступ ко всем задачам всех сотрудников,
            // делаем выборку только по user_id текущего пользователя
            if ($success != 1) {
                $this->db->where('(responsible_task.who_insert_id = ' . USER_COOKIE_ID .
                    ' OR responsible_task.user_id = ' . USER_COOKIE_ID .
                    ' OR multitask.iniciator = ' . USER_COOKIE_ID .
                    ' OR multitask.kurator = ' . USER_COOKIE_ID .
                    ' OR wtchrs_ids = ' . USER_COOKIE_ID .
                    ')');
            }

            $this->db->group_by("multitask.id");
            //--- Сортировка -----------------------------------------------------------------------------------------------
            if (isset($post['order'])) {
                $orderFieldName = $post['order']['fieldName'];
                $orderDirection = $post['order']['direction'];
            } else {
                $orderFieldName = 'multitask.id';
                $orderDirection = 'DESC';
            }
            $this->db->order_by($orderFieldName, $orderDirection);
            //--------------------------------------------------------------------------------------------------------------


        if ($calcCount){
                $query = $this->db->get();
                $result = $query->num_rows();
        } else {
            if (isset($_COOKIE['count_load']) && isset($load['load']) && !empty($_COOKIE['count_load']) && $_COOKIE['count_load'] != -1 ) {
                $this->db->limit($_COOKIE['count_load'], $load['load']);
            } else {
                $this->db->limit(25, 0);
            }

            $query = $this->db->get();
            $result = $query->result();

        }

        return $result;
    }
