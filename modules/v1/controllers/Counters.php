<?php
defined('PATHACCESS') OR exit('No access');

/**
 * @OA\Info(title="Apsis RnD assingment", version="1.0")
 */

class Counters {


  /**
  * @OA\Post(
  *     path="/v1/counters/add",
  *     summary="Creating a new counter (Task 1)",
  *     tags={"Counters"},
  *     @OA\RequestBody(
  *        @OA\MediaType(
  *            mediaType="multipart/form-data",
  *            @OA\Schema(
  *                @OA\Property(
  *                    property="employee_id",
  *                    type="integer",
  *                    required=true,
  *                ),
  *                @OA\Property(
  *                    property="name",
  *                    type="string",
  *                    required=true,
  *                ),
  *                @OA\Property(
  *                    property="team_id",
  *                    type="integer",
  *                    required=true,
  *                ),
  *            ),
  *        ),
  *     ),
  *     @OA\Response(response="409", description="Missed parameters"),
  *     @OA\Response(response="226", description="This name is already used"),
  *     @OA\Response(response="409", description="No access to perform this action"),
  *     @OA\Response(response="201", description="A new counter created"),
  * )
  */
  function add() {
    $params = [
      "employee_id" => Input::post("employee_id"),
      "name"        => Input::post("name"),
      "team_id"     => Input::post("team_id")
    ];
    Validation::validateArray($params,["employee_id", "name", "team_id"]);

    Validation::checkExist($params["employee_id"], "employees");

    Validation::checkExist($params["team_id"], "teams", "session");

    $counters = Session::get("counters") ?: [];

    if (in_array($params["name"],array_map(function($i) {return $i["name"];},$counters))) {
      Response::json(
        Response::rest(
          226,
          "This name is already used"
        )
      );
    }

    $teams = Session::get("teams");
    foreach ($teams as $team) {
      if ((int)$team["id"] === (int)$params["team_id"] && !in_array((int)$params["employee_id"],$team["employee_ids"])) {
        Response::json(
          Response::rest(
            409,
            "No access to perform this action"
          )
        );
      }
    }


    $counters[] = [
      "id"            => $counters ? max(array_map(function($i) {return $i["id"];},$counters)) + 1 : 1,
      "name"          => $params["name"],
      "team_id"       => $params["team_id"],
      "steps"         => 0
    ];

    Session::set("counters",$counters);


    Response::json(
      Response::rest(
        201,
        "A new counter created"
      )
    );
  }



  /**
  * @OA\Post(
  *     path="/v1/counters/{id}/increment",
  *     summary="Increment steps (Task 2)",
  *     tags={"Counters"},
  *     @OA\Parameter(
  *         in = "path",
  *         name="id",
  *         description="",
  *         required=true,
  *         @OA\Schema(
  *             type="integer"
  *         )
  *     ),
  *     @OA\RequestBody(
  *        @OA\MediaType(
  *            mediaType="multipart/form-data",
  *            @OA\Schema(
  *                @OA\Property(
  *                    property="employee_id",
  *                    type="integer",
  *                    required=true,
  *                ),
  *            ),
  *        ),
  *     ),
  *     @OA\Response(response="409", description="Missed parameters"),
  *     @OA\Response(response="204", description="This name is already used"),
  *     @OA\Response(response="201", description="A new counter created"),
  * )
  */
  function incrementSteps($id = NULL) {
    if (!$id) {
      Response::json(
        Response::rest(
          409,
          "ID is required"
        )
      );
    }

    $params = [
      "employee_id" => Input::post("employee_id"),
    ];

    Validation::validateArray($params,["employee_id"]);

    Validation::checkExist($params["employee_id"], "employees");

    Validation::checkExist($id, "counters", "session");

    $counters   = Session::get("counters");

    $teams      = Session::get("teams");
    $teams_ids  = [];
    foreach ($teams as $team) {
      if (in_array($params["employee_id"], $team["employee_ids"])) {
        $teams_ids[] = $team["id"];
      }
    }

    $is_team_member = FALSE;
    foreach ($counters as $key => &$counter) {
      if ((int)$counter["id"] === (int)$id) {
        if (in_array($counter["team_id"], $teams_ids)) {
          $is_team_member = TRUE;
          $counter["steps"]++;
        }
      }
    }

    if (!$is_team_member) {
      Response::json(
        Response::rest(
          409,
          "No access to perform this action"
        )
      );
    }

    Session::set("counters",$counters);

    Response::json(
      Response::rest(
        202,
        "The counter incremented"
      )
    );
  }



  /**
  * @OA\Delete(
  *     path="/v1/counters/{id}/delete",
  *     summary="Delete counter (Task 7)",
  *     tags={"Counters"},
  *     @OA\Parameter(
  *         in = "path",
  *         name="id",
  *         description="",
  *         required=true,
  *         @OA\Schema(
  *             type="integer"
  *         )
  *     ),
  *     @OA\Response(response="409", description="ID is required"),
  *     @OA\Response(response="204", description="The counter not found"),
  *     @OA\Response(response="202", description="The counter removed"),
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

    $counters = Session::get("counters") ?: [];

    $counter_exist = FALSE;
    foreach ($counters as $key => $counter) {
      if ((int)$counter["id"] === (int)$id) {
        $counter_exist = TRUE;
        unset($counters[$key]);
      }
    }

    if (!$counter_exist) {
      Response::json(
        Response::rest(
          204,
          "The counter not found"
        )
      );
    }

    Session::set("counters",array_values($counters));

    Response::json(
      Response::rest(
        202,
        "The counter removed"
      )
    );
  }


}
