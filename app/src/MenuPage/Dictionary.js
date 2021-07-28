import React from "react";
import { Link } from "react-router-dom";
import styled from "styled-components";
import ColumnMeter from "../components/ColumnMeter";

const Container = styled.section`
  display: inline-block;
  padding: 1em;
  margin-bottom: 0.5em;
  & dl {
    display: grid;
    grid-template-columns: max-content max-content;
  }
  & dd {
    text-align: right;
  }
`;

const EditLinks = styled.div`
  font-size: smaller;
  & a + a {
    margin-left: 0.3em;
  }
`;

const TestLink = styled(Link)`
  display: inline-block;
  font-size: 14px;
  padding: 6px 12px;
  border: 1px solid #e0e0e0;
  border-radius: 3px;
  background-color: #334ba2;
  border: none;
  color: white;
  cursor: pointer;
  text-decoration: none;
  margin-top: 0.5em;
`;

function Dictionary({ dict }) {
  return (
    <Container>
      <h3>{dict.name}</h3>
      <EditLinks>
        <Link to={`dicts/${dict.id}`}>Edit</Link>
        <Link to={`/${dict.id}/add`}>Add words</Link>
      </EditLinks>
      <Stats stats={dict.stats} />
      <TestLink to={`/${dict.id}/test`}>Do a test</TestLink>{" "}
    </Container>
  );
}

function Stats(props) {
  const { stats } = props;
  const { pairs, touched, finished } = stats;

  return (
    <div>
      <dl>
        <dt>Number of entries</dt>
        <dd>{pairs}</dd>

        <dt>Finished</dt>
        <dd>{finished}</dd>

        <dt>Touched</dt>
        <dd>{touched}</dd>

        <dt>Success rate</dt>
        <dd>{(stats.successRate * 100).toFixed(1)} %</dd>
      </dl>
      <ColumnMeter
        inQueue={pairs - touched}
        inProgress={touched}
        finished={finished}
      />
    </div>
  );
}

export default Dictionary;
