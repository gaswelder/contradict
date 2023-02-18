import React, { useEffect, useState } from "react";
import { withRouter } from "react-router";
import styled from "styled-components";
import withAPI from "../components/withAPI";

const ContainerDiv = styled.div`
  text-align: center;
`;

const Card = styled.div`
  padding: 40px;
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  max-width: 20em;
  border: 1px solid #eef;
  margin: 2em auto;
  background-color: ${(props) => (props.reverse ? "#ffd9e0" : "white")};
  position: relative;
  min-height: 6em;
  display: flex;
  align-items: center;
  justify-content: center;
  & .corner {
    position: absolute;
    opacity: 0.7;
    right: 10px;
    top: 10px;
    font-size: 90%;
  }
`;

const shuffle = (xs) =>
  xs
    .map((x) => [Math.random(), x])
    .sort((a, b) => a[0] - b[0])
    .map((x) => x[1]);

export const RepetitionsPage = withRouter(
  withAPI(({ api, match, busy }) => {
    const dictId = match.params.id;
    const [cards, setCards] = useState([]);
    const [show, setShow] = useState(false);

    const nextBatch = async () => {
      const r = await api.test(dictId);
      setCards(shuffle([...r.tuples1, ...r.tuples2]));
    };

    const next = async () => {
      setShow(false);
      if (cards.length == 1) {
        await nextBatch();
      } else {
        setCards(cards.slice(1));
      }
    };

    useEffect(() => {
      nextBatch();
    }, []);

    const cc = cards[0];
    if (!cc) {
      return "loading";
    }
    return (
      <ContainerDiv>
        <Card
          reverse={cc.reverse}
          onClick={() => {
            if (show) {
              return;
            }
            setShow(true);
            api.touchCard(cc.id, cc.reverse, true);
          }}
        >
          <div className="corner">{cc.times}</div>
          <div>
            {cc.q}
            {cc.hint && ` (${cc.hint})`}
            {show && (
              <p>
                {cc.a} <a href={cc.wikiURL}>wiki</a>
              </p>
            )}
          </div>
        </Card>
        {show && <button onClick={next}>Next</button>}
        {!show && (
          <>
            <button
              disabled={busy}
              onClick={() => {
                api.touchCard(cc.id, cc.reverse, true);
                setShow(true);
              }}
            >
              Yes, know it
            </button>{" "}
            <button
              onClick={async () => {
                api.touchCard(cc.id, cc.reverse, true);
                setShow(true);
              }}
            >
              No, forgot it
            </button>
          </>
        )}
      </ContainerDiv>
    );
  })
);
