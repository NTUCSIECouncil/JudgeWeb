function Req_data(id, offset) {
    $.ajax({
        url: "admin_sb4_ajax.php",
        type: "POST",
        data: {
            "timestamp_offset": offset,
            "contest_id": id
        },
        success: function(data, status, req) {
            updatetable(data);
        },
        dataType: "JSON"
    });
}

function updatetable(data) {
    let tbody = $("#Ranktable");
    data.forEach((value, key) => {
        let tr = $("<tr>");
        tr.append(
            $("<td>", { "text": key }),
            $("<td>", { "text": value.team.slice(0,10)+"..." }),
            $("<td>", {
                "align": "center",
                "text": value.solved
            }),
            $("<td>", {
                "style": "border-right: 2px dashed black;",
                "align": "center",
                "text": value.penalty
            })
        );
        for (let index = 0; index < prob_n; index++) {
            tr.append(
                $("<td>", { "align": "center", "text": value[index] })
            );
        }
        tbody.append(tr);
    });
    console.log(data);
    
}
