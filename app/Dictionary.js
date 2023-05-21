import React from "react";
import { Link } from "react-router-dom";
import styled from "styled-components";
import { ROOT_PATH } from "./api";
import { ButtonLink } from "./lib/ButtonLink";

const RootDiv = styled.section`
  padding: 1em;
  margin-bottom: 0.5em;
  display: inline-block;
  margin-right: 0.5em;
  border-radius: 4px;
`;

const HeaderDiv = styled.div`
  font-size: smaller;
  & a + a {
    margin-left: 0.3em;
  }
  padding: 1em 0;
  display: flex;
  & h3 {
    margin: 0 auto 0 0;
    font-size: 14pt;
  }
`;

const ColumnMeterDiv = styled.div`
  position: relative;
  display: flex;
  & > div {
    overflow: hidden;
    font-size: 10pt;
    text-indent: 2px;
    white-space: nowrap;
    text-align: right;
    padding: 4px 0;
  }
  & .finished {
    background-color: #b9de96;
  }
  & .in-progress {
    background-color: #b0f1ff;
  }
  & .in-queue {
    background-color: #eee;
  }
`;

function Dictionary({ dict }) {
  const { total, finished, inProgress } = dict.stats;
  const pos = (n) => (100 * n) / total + "%";
  return (
    <RootDiv>
      <HeaderDiv>
        <h3>{dict.name}</h3>
        <Link to={`${ROOT_PATH}stats/${dict.id}`}>Stats</Link>{" "}
        <Link to={`${ROOT_PATH}dicts/${dict.id}`}>Edit</Link>
      </HeaderDiv>
      <ColumnMeterDiv>
        <div
          className="finished"
          style={{ width: pos(finished) }}
          title={"finished: " + finished}
        >
          &nbsp;
        </div>
        <div
          className="in-progress"
          style={{ width: pos(inProgress) }}
          title={"in progress: " + inProgress}
        >
          &nbsp;
        </div>
        <div className="in-queue" style={{ width: pos(total) }} title="total">
          {total}
        </div>
      </ColumnMeterDiv>
      <div style={{ marginTop: "0.5em" }}>
        <ButtonLink to={`${ROOT_PATH}${dict.id}/repetitions`}>
          Repetitions
        </ButtonLink>{" "}
        <ButtonLink to={`${ROOT_PATH}${dict.id}/sheet`}>Sheet</ButtonLink>{" "}
        <ButtonLink to={`${ROOT_PATH}${dict.id}/add`}>Add Cards</ButtonLink>
      </div>
    </RootDiv>
  );
}

export default Dictionary;
