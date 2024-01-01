import React, { useEffect, useState } from "react";
import { BrowserRouter, Route, Switch } from "react-router-dom";
import AddEntriesPage from "./AddEntriesPage";
import api, { ROOT_PATH } from "./api";
import { DictPage } from "./DictPage";
import Export from "./Export";
import LoginPage from "./LoginPage";
import MenuPage from "./MenuPage";
import { Page } from "./Page";
import { RepetitionsPage } from "./RepetitionsPage";
import { ResultsPage } from "./ResultsPage";
import { SheetPage } from "./SheetPage";
import { StatsPage } from "./StatsPage";

function page(Component, header = true) {
  const wrappedPage = (props) => (
    <Page header={header}>
      <Component {...props} />
    </Page>
  );
  return wrappedPage;
}

class Main extends React.Component {
  render() {
    const R = ROOT_PATH;
    return (
      <BrowserRouter>
        <Switch>
          <Route
            exact
            path={`${R}`}
            component={(props) => {
              return (
                <Page back={<></>} header>
                  <MenuPage {...props} />
                </Page>
              );
            }}
          />
          <Route
            path={`${R}:id/sheet`}
            component={page(({ match }) => {
              return <SheetPage dictID={match.params.id} />;
            })}
          />
          <Route
            path={`${R}stats/:id`}
            component={page(({ match }) => {
              return <StatsPage dictID={match.params.id} />;
            })}
          />
          <Route
            path={`${R}:id/repetitions`}
            component={page(({ match }) => {
              return <RepetitionsPage dictID={match.params.id} />;
            })}
          />
          <Route
            path={`${R}:id/results`}
            component={page(({ match }) => {
              return <ResultsPage id={match.params.id} />;
            })}
          />
          <Route
            path={`${R}:id/add`}
            component={({ match }) => {
              const dictID = match.params.id;
              const [dict, setDict] = useState(null);
              const [err, setErr] = useState(null);
              useEffect(() => {
                setDict(null);
                api.dict(dictID).then(setDict).catch(setErr);
              }, [dictID]);
              if (err) return `Error: ${err.message}`;
              if (!dict) return <Page header>Loading</Page>;
              return (
                <Page header headerContent={dict.name}>
                  <AddEntriesPage dictID={dictID} />
                </Page>
              );
            }}
          />
          <Route
            path={`${R}dicts/:id`}
            component={page(({ match }) => {
              return <DictPage dictID={match.params.id} />;
            })}
          />
          <Route path={`${R}login`} component={page(LoginPage, false)} />
          <Route path={`${R}export`} component={page(Export)} />
          <Route component={page(() => "Not Found")} />
        </Switch>
      </BrowserRouter>
    );
  }
}

export default Main;
