import React from "react";
import { Link } from "react-router-dom";
import styled from "styled-components";
import { ROOT_PATH } from "./api";
import { ButtonLink } from "./ButtonLink";

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
    background-color: #b0f1ff;
  }
  & .in-progress {
    background-color: #b9de96;
  }
  & .in-queue {
    background-color: #eee;
  }
`;

function Dictionary({ dict }) {
  const { pairs, touched, finished } = dict.stats;
  const total = pairs + finished;
  const pos = (n) => (100 * n) / total + "%";
  return (
    <RootDiv>
      <HeaderDiv>
        <h3>{dict.name}</h3>
        <Link to={`${ROOT_PATH}dicts/${dict.id}`}>Edit</Link>
        <Link to={`${ROOT_PATH}${dict.id}/add`}>Add words</Link>
      </HeaderDiv>
      <ColumnMeterDiv>
        <div
          className="finished"
          style={{ width: pos(finished) }}
          title="finished"
        >
          {finished}
        </div>
        <div
          className="in-progress"
          style={{ width: pos(touched) }}
          title="touched"
        >
          {finished + touched}
        </div>
        <div className="in-queue" style={{ width: pos(total) }} title="total">
          {total}
        </div>
      </ColumnMeterDiv>
      <div style={{ marginTop: "0.5em" }}>
        <ButtonLink to={`${ROOT_PATH}${dict.id}/test`}>Do a test</ButtonLink>{" "}
        <ButtonLink to={`${ROOT_PATH}${dict.id}/repetitions`}>
          Do repetitions
        </ButtonLink>{" "}
      </div>
    </RootDiv>
  );
}

export default Dictionary;
