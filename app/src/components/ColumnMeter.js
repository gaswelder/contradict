import React from "react";
import "./ColumnMeter.css";

function ColumnMeter(props) {
  const { inQueue, inProgress, finished } = props;

  const total = inQueue + inProgress + finished;

  return (
    <div className="column-meter">
      <div
        className="finished"
        style={{ width: (100 * finished) / total + "%" }}
      >
        finished
      </div>

      <div
        className="in-progress"
        style={{ width: (100 * inProgress) / total + "%" }}
      >
        in progress
      </div>
      <div
        className="in-queue"
        style={{ width: (100 * inQueue) / total + "%" }}
      >
        in queue
      </div>
    </div>
  );
}

export default ColumnMeter;
