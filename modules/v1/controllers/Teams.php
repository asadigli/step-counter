<?php
defined('PATHACCESS') OR exit('No access');


/**
 * @OA\Info(title="Apsis RnD assingment", version="1.0")
 */

class Teams {

  /**
  * @OA\Post(
  *     path="/v1/teams/add",
  *     summary="Creating a new counter (Task 6)",
  *     tags={"Teams"},
  *     @OA\RequestBody(
  *        @OA\MediaType(
  *            mediaType="multipart/form-data",
  *            @OA\Schema(
  *                @OA\Property(
  *                    property="employee_ids",
  *                    type="array",
  *                    required=true,
  *                ),
  *                @OA\Property(
  *                    property="name",
  *                    type="string",
  *                    required=true,
  *                ),
  *            ),
  *        ),
  *     ),
  *     @OA\Response(response="409", description="Missed parameters"),
  *     @OA\Response(response="226", description="This name is already used"),
  *     @OA\Response(response="201", description="A new team created"),
  * )
  */
  function add() {
    $params = [
      "employee_ids"  => Input::post("employee_ids"),
      "name"          => Input::post("name"),
    ];
    $params["employee_ids"] = is_array($params["employee_ids"]) ? $params["employee_ids"] : explode(",",$params["employee_ids"]);

    Validation::validateArray($params,["employee_ids", "name"]);

    foreach ($params["employee_ids"] as $item) {
      Validation::checkExist($item, "employees");
    }
    $teams = Session::get("teams") ?: [];

    if (in_array($params["name"],array_map(function($i) {return $i["name"];},$teams))) {
      Response::json(
        Response::rest(
          226,
          "This name is already used"
        )
      );
    }

    $teams[] = [
      "id"            => $teams ? max(array_map(function($i) {return $i["id"];},$teams)) + 1 : 1,
      "name"          => $params["name"],
      "employee_ids"  => $params["employee_ids"]
    ];

    Session::set("teams",$teams);

    Response::json(
      Response::rest(
        201,
        "A new team created"
      )
    );
  }


  /**
  * @OA\Get(
  *     path="/v1/teams/{id}/steps",
  *     summary="Team list (Task 3)",
  *     tags={"Teams"},
  *     @OA\Parameter(
  *         in = "path",
  *         name="id",
  *         description="",
  *         required=true,
  *         @OA\Schema(
  *             type="integer"
  *         )
  *     ),
  *     @OA\Response(response="409", description="Missed parameters"),
  *     @OA\Response(response="204", description="This name is already used"),
  *     @OA\Response(response="201", description="A new counter created"),
  * )
  */
  function steps($id = NULL) {
    if (!$id) {
      Response::json(
        Response::rest(
          409,
          "ID is required"
        )
      );
    }

    $teams = Session::get("teams") ?: [];
    $team_details = [];
    foreach ($teams as $key => &$team) {
      unset($team["employee_ids"]);
      if ((int)$id === (int)$team["id"]) {
        $team["steps"] = 0;
        $team_details = $team;
      }
    }

    if (!$team_details) {
      Response::json(
        Response::rest(
          204,
          "No team found"
        )
      );
    }

    $counters = Session::get("counters") ?: [];

    foreach ($counters as $counter) {
      if ((int)$counter["team_id"] !== (int)$id) continue;
      $team_details["steps"] += $counter["steps"];
    }


    Response::json(
      Response::rest(
        200,
        "Success",
        $team_details
      )
    );
  }


  /**
  * @OA\Get(
  *     path="/v1/teams/{id}/counters",
  *     summary="Team list (Task 5)",
  *     tags={"Teams"},
  *     @OA\Parameter(
  *         in = "path",
  *         name="id",
  *         description="",
  *         required=true,
  *         @OA\Schema(
  *             type="integer"
  *         )
  *     ),
  *     @OA\Response(response="409", description="Missed parameters"),
  *     @OA\Response(response="204", description="This name is already used"),
  *     @OA\Response(response="201", description="A new counter created"),
  * )
  */
  function counters($id = NULL) {
    if (!$id) {
      Response::json(
        Response::rest(
          409,
          "ID is required"
        )
      );
    }

    $teams = Session::get("teams") ?: [];
    $team_exist = FALSE;
    foreach ($teams as $key => $team) {
      if ((int)$id === (int)$team["id"]) {
        $team_exist = TRUE;
      }
    }

    if (!$team_exist) {
      Response::json(
        Response::rest(
          204,
          "No team found"
        )
      );
    }

    $counters = Session::get("counters") ?: [];

    foreach ($counters as $key => &$counter) {
      if ((int)$counter["team_id"] !== (int)$id) unset($counters[$key]);
      unset($counter["team_id"]);
    }


    Response::json(
      Response::rest(
        200,
        "Success",
        array_values($counters)
      )
    );
  }


  /**
  * @OA\Get(
  *     path="/v1/teams/all",
  *     summary="Team list (Task 4)",
  *     tags={"Teams"},
  *     @OA\Response(response="409", description="Missed parameters"),
  *     @OA\Response(response="204", description="This name is already used"),
  *     @OA\Response(response="201", description="A new counter created"),
  * )
  */
  function all() {
    $teams = Session::get("teams") ?: [];

    if (!$teams) {
      Response::json(
        Response::rest(
          204,
          "No team found"
          )
      );
    }

    $counters = Session::get("counters");

    $counters_key_value = [];
    foreach ($counters as $counter) {
      if (!isset($counters_key_value[$counter["team_id"]])) {
        $counters_key_value[$counter["team_id"]] = 0;
      }
      $counters_key_value[$counter["team_id"]] += $counter["steps"];
    }

    foreach ($teams as &$team) {
      $team["steps"] = @$counters_key_value[$team["id"]] ?: 0;
      unset($team["employee_ids"]);
    }

    Response::json(
      Response::rest(
        200,
        "Success",
        $teams
      )
    );
  }


  /**
  * @OA\Delete(
  *     path="/v1/teams/{id}/delete",
  *     summary="Delete counter (Task 6)",
  *     tags={"Teams"},
  *     @OA\Parameter(
  *         in = "path",
  *         name="id",
  *         description="",
  *         required=true,
  *         @OA\Schema(
  *             type="integer"
  *         )
  *     ),
  *     @OA\Response(response="409", description="Missed parameters"),
  *     @OA\Response(response="204", description="This name is already used"),
  *     @OA\Response(response="201", description="A new counter created"),
  * )
  */
  function delete($id = NULL) {
    if (!$id) {
      Response::json(
        Response::rest(
          409,
          "ID is required"
        )
      );
    }

    $teams = Session::get("teams") ?: [];

    $team_exist = FALSE;
    foreach ($teams as $key => $team) {
      if ((int)$team["id"] === (int)$id) {
        $team_exist = TRUE;
        unset($teams[$key]);
      }
    }

    $counters = Session::get("counters") ?: [];

    $counter_exist = FALSE;
    foreach ($counters as $key => $counter) {
      if ((int)$counter["team_id"] === (int)$id) {
        $counter_exist = TRUE;
        unset($counters[$key]);
      }
    }

    if (!$team_exist) {
      Response::json(
        Response::rest(
          204,
          "The team not found"
        )
      );
    }

    Session::set("teams",array_values($teams));

    Response::json(
      Response::rest(
        202,
        "The team removed"
        )
    );
  }

}
