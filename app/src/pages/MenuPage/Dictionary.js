import React from "react";
import { Link } from "react-router-dom";
import styled from "styled-components";
import { ButtonLink } from "../../components/ButtonLink";
import "./ColumnMeter.css";

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

function Dictionary({ dict }) {
  const { pairs, touched, finished } = dict.stats;
  const total = pairs + finished;
  const pos = (n) => (100 * n) / total + "%";
  return (
    <RootDiv>
      <HeaderDiv>
        <h3>{dict.name}</h3>
        <Link to={`dicts/${dict.id}`}>Edit</Link>
        <Link to={`/${dict.id}/add`}>Add words</Link>
      </HeaderDiv>
      <div className="column-meter">
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
      </div>
      <div style={{ marginTop: "0.5em" }}>
        <ButtonLink to={`/${dict.id}/test`}>Do a test</ButtonLink>{" "}
        <ButtonLink to={`/${dict.id}/repetitions`}>Do repetitions</ButtonLink>{" "}
      </div>
    </RootDiv>
  );
}

export default Dictionary;
