import React from "react";
import { BrowserRouter, Route, Switch, Link } from "react-router-dom";
import AddEntriesPage from "./AddEntriesPage";
import EntryPage from "./EntryPage";
import TestPage from "./TestPage";
import LoginPage from "./LoginPage";
import MenuPage from "./MenuPage";
import api from "./api";

async function logout() {
  await api.logout();
  location.href = "/login";
}

function Header() {
  return (
    <header>
      <Link to="/">Dict</Link>
      <button className="logout" onClick={logout}>
        Logout
      </button>
    </header>
  );
}

function page(Component, header = true) {
  return function page(props) {
    return (
      <main className="page">
        {header && <Header />}
        <Component {...props} />
      </main>
    );
  };
}

class Main extends React.Component {
  render() {
    return (
      <BrowserRouter>
        <Switch>
          <Route exact path="/" component={page(MenuPage)} />
          <Route path="/:id/test" component={page(TestPage)} />
          <Route path="/:id/add" component={page(AddEntriesPage)} />
          <Route path="/entries/:id" component={page(EntryPage)} />
          <Route path="/login" component={page(LoginPage, false)} />
          <Route component={page(() => "Not Found")} />
        </Switch>
      </BrowserRouter>
    );
  }
}

export default Main;
